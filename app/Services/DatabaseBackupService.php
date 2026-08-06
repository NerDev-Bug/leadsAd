<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use RuntimeException;

class DatabaseBackupService
{
    public const BACKUP_DIR = 'backups';

    public const MANIFEST = 'manifest.json';

    /**
     * @return array{overview: array<string, mixed>, backups: list<array<string, mixed>>}
     */
    public function index(): array
    {
        $backups = $this->allBackups();

        $last = $backups[0] ?? null;

        return [
            'overview' => [
                'last_backup' => $last,
                'total' => count($backups),
                'next_scheduled' => null,
            ],
            'backups' => $backups,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function allBackups(): array
    {
        $manifest = $this->readManifest();
        $items = [];

        foreach ($manifest as $entry) {
            $path = $this->backupPath($entry['filename'] ?? '');
            if (!is_file($path)) {
                continue;
            }

            $items[] = $this->formatEntry($entry, $path);
        }

        usort($items, fn ($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));

        return $items;
    }

    /**
     * @return array<string, mixed>
     */
    public function createManual(string $createdBy): array
    {
        return $this->createBackup('Manual', $createdBy);
    }

    /**
     * @return array<string, mixed>
     */
    public function createBackup(string $type, string $createdBy): array
    {
        $this->ensureBackupDirectory();

        $timestamp = now()->format('Y_m_d_His');
        $filename = "backup_{$timestamp}.sql";
        $path = $this->backupPath($filename);

        $this->dumpDatabase($path);

        if (!is_file($path) || filesize($path) === 0) {
            @unlink($path);
            throw new RuntimeException('Backup file was not created or is empty.');
        }

        $entry = [
            'id' => pathinfo($filename, PATHINFO_FILENAME),
            'filename' => $filename,
            'type' => $type,
            'created_by' => $createdBy,
            'created_at' => now()->toIso8601String(),
        ];

        $manifest = $this->readManifest();
        array_unshift($manifest, $entry);
        $this->writeManifest($manifest);

        return $this->formatEntry($entry, $path);
    }

    public function delete(string $filename): void
    {
        $filename = $this->sanitizeFilename($filename);
        $path = $this->backupPath($filename);

        if (is_file($path)) {
            @unlink($path);
        }

        $manifest = array_values(array_filter(
            $this->readManifest(),
            fn ($row) => ($row['filename'] ?? '') !== $filename
        ));
        $this->writeManifest($manifest);
    }

    public function resolveDownloadPath(string $filename): string
    {
        $filename = $this->sanitizeFilename($filename);
        $path = $this->backupPath($filename);

        if (!is_file($path)) {
            throw new RuntimeException('Backup file not found.');
        }

        return $path;
    }

    public function backupDirectory(): string
    {
        return storage_path('app/' . self::BACKUP_DIR);
    }

    protected function backupPath(string $filename): string
    {
        return $this->backupDirectory() . DIRECTORY_SEPARATOR . $filename;
    }

    protected function ensureBackupDirectory(): void
    {
        $dir = $this->backupDirectory();
        if (!is_dir($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
    }

    protected function sanitizeFilename(string $filename): string
    {
        $filename = basename($filename);
        if (!preg_match('/^backup_[0-9]{4}_[0-9]{2}_[0-9]{2}_[0-9]{6}\.sql$/', $filename)) {
            throw new RuntimeException('Invalid backup filename.');
        }

        return $filename;
    }

    protected function dumpDatabase(string $destinationPath): void
    {
        $connection = config('database.default');
        $config = config("database.connections.{$connection}");

        if (!$config) {
            throw new RuntimeException('Database connection is not configured.');
        }

        if (($config['driver'] ?? '') === 'sqlite') {
            $this->dumpSqlite($config['database'], $destinationPath);

            return;
        }

        if (in_array($config['driver'] ?? '', ['mysql', 'mariadb'], true)) {
            $this->dumpMysql($config, $destinationPath);

            return;
        }

        throw new RuntimeException('Unsupported database driver for backup.');
    }

    protected function dumpMysql(array $config, string $destinationPath): void
    {
        $usePdo = filter_var(
            env('DB_BACKUP_USE_PDO', PHP_OS_FAMILY === 'Windows'),
            FILTER_VALIDATE_BOOLEAN
        );

        if ($usePdo) {
            $this->dumpMysqlViaPdo($destinationPath);

            return;
        }

        try {
            $this->dumpMysqlViaProcess($config, $destinationPath);
        } catch (RuntimeException) {
            $this->dumpMysqlViaPdo($destinationPath);
        }
    }

    /**
     * @param  array<string, mixed>  $config
     */
    protected function dumpMysqlViaProcess(array $config, string $destinationPath): void
    {
        $binary = $this->mysqldumpBinary();
        $args = [
            $binary,
            '--host=' . ($config['host'] ?? '127.0.0.1'),
            '--port=' . ($config['port'] ?? '3306'),
            '--user=' . ($config['username'] ?? 'root'),
            '--single-transaction',
            '--routines',
            '--triggers',
            $config['database'] ?? '',
        ];

        $password = $config['password'] ?? '';
        if ($password !== '') {
            $args[] = '--password=' . $password;
        }

        $result = Process::timeout(300)->run($args);

        if (!$result->successful()) {
            throw new RuntimeException(trim($result->errorOutput() ?: $result->output() ?: 'mysqldump failed.'));
        }

        file_put_contents($destinationPath, $result->output());
    }

    protected function dumpMysqlViaPdo(string $destinationPath): void
    {
        $handle = fopen($destinationPath, 'wb');
        if ($handle === false) {
            throw new RuntimeException('Could not open backup file for writing.');
        }

        $pdo = DB::connection()->getPdo();
        $database = DB::getDatabaseName();

        fwrite($handle, "-- LAPC database backup (PDO)\n");
        fwrite($handle, '-- Generated: ' . now()->toDateTimeString() . "\n\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n");
        fwrite($handle, "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n\n");

        $tables = DB::select('SHOW FULL TABLES WHERE Table_type = "BASE TABLE"');
        $tableKey = 'Tables_in_' . $database;

        foreach ($tables as $tableRow) {
            $tableName = $tableRow->{$tableKey} ?? null;
            if (!$tableName) {
                continue;
            }

            $createRows = DB::select('SHOW CREATE TABLE `' . str_replace('`', '``', $tableName) . '`');
            $createSql = $createRows[0]->{'Create Table'} ?? null;
            if (!$createSql) {
                continue;
            }

            fwrite($handle, "\n-- Table `{$tableName}`\n");
            fwrite($handle, "DROP TABLE IF EXISTS `{$tableName}`;\n");
            fwrite($handle, $createSql . ";\n\n");

            foreach (DB::table($tableName)->cursor() as $row) {
                $data = (array) $row;
                $columns = array_map(
                    fn (string $column) => '`' . str_replace('`', '``', $column) . '`',
                    array_keys($data)
                );
                $values = array_map(function ($value) use ($pdo) {
                    if ($value === null) {
                        return 'NULL';
                    }

                    return $pdo->quote((string) $value);
                }, array_values($data));

                fwrite(
                    $handle,
                    'INSERT INTO `' . $tableName . '` (' . implode(', ', $columns) . ') VALUES ('
                    . implode(', ', $values) . ");\n"
                );
            }

            fwrite($handle, "\n");
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($handle);
    }

    protected function dumpSqlite(string $databasePath, string $destinationPath): void
    {
        if (!is_file($databasePath)) {
            throw new RuntimeException('SQLite database file not found.');
        }

        copy($databasePath, $destinationPath);
    }

    protected function mysqldumpBinary(): string
    {
        $configured = env('MYSQL_DUMP_PATH');
        if ($configured && file_exists($configured)) {
            return $configured;
        }

        foreach ([
            'C:\\xampp\\mysql\\bin\\mysqldump.exe',
            'C:\\xampp\\mysql\\bin\\mysqldump',
        ] as $candidate) {
            if (file_exists($candidate)) {
                return $candidate;
            }
        }

        return 'mysqldump';
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function readManifest(): array
    {
        $this->ensureBackupDirectory();
        $path = $this->backupDirectory() . DIRECTORY_SEPARATOR . self::MANIFEST;

        if (!is_file($path)) {
            return [];
        }

        $data = json_decode((string) file_get_contents($path), true);

        return is_array($data) ? $data : [];
    }

    /**
     * @param  list<array<string, mixed>>  $manifest
     */
    protected function writeManifest(array $manifest): void
    {
        $path = $this->backupDirectory() . DIRECTORY_SEPARATOR . self::MANIFEST;
        file_put_contents($path, json_encode(array_values($manifest), JSON_PRETTY_PRINT));
    }

    /**
     * @param  array<string, mixed>  $entry
     * @return array<string, mixed>
     */
    protected function formatEntry(array $entry, string $path): array
    {
        $bytes = (int) filesize($path);

        return [
            'id' => $entry['id'] ?? pathinfo($entry['filename'] ?? '', PATHINFO_FILENAME),
            'filename' => $entry['filename'] ?? basename($path),
            'type' => $entry['type'] ?? 'Manual',
            'created_by' => $entry['created_by'] ?? 'System',
            'created_at' => $entry['created_at'] ?? null,
            'size' => $bytes,
            'size_label' => $this->formatBytes($bytes),
            'date_label' => $entry['created_at']
                ? now()->parse($entry['created_at'])->timezone(config('app.timezone'))->format('M j, Y h:i A')
                : '—',
        ];
    }

    protected function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        }

        if ($bytes < 1024 * 1024) {
            return round($bytes / 1024, 1) . ' KB';
        }

        return round($bytes / (1024 * 1024), 1) . ' MB';
    }
}
