<?php

namespace App\Http\Controllers;

use App\Services\DatabaseBackupService;
use Illuminate\Http\Request;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BackupController extends Controller
{
    public function __construct(
        protected DatabaseBackupService $backups
    ) {}

    public function index()
    {
        return response()->json($this->backups->index());
    }

    public function store(Request $request)
    {
        $createdBy = $request->user()?->username
            ?? $request->user()?->email
            ?? 'Admin';

        try {
            $backup = $this->backups->createManual($createdBy);

            return response()->json([
                'message' => 'Backup created successfully.',
                'backup' => $backup,
                'data' => $this->backups->index(),
            ], 201);
        } catch (RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function download(string $filename): BinaryFileResponse
    {
        try {
            $path = $this->backups->resolveDownloadPath($filename);
        } catch (RuntimeException $e) {
            abort(404, $e->getMessage());
        }

        return response()->download($path, basename($path), [
            'Content-Type' => 'application/sql',
        ]);
    }

    public function destroy(string $filename)
    {
        try {
            $this->backups->delete($filename);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Backup deleted.',
            'data' => $this->backups->index(),
        ]);
    }
}
