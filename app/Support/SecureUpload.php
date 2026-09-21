<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use RuntimeException;

class SecureUpload
{
    /**
     * @var list<string>
     */
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];

    /**
     * Store an image under public/{directory} with a random safe filename.
     */
    public static function storeImage(UploadedFile $file, string $directory, string $prefix = ''): string
    {
        $directory = trim($directory, '/\\');
        $destination = public_path($directory);

        if (! is_dir($destination) && ! @mkdir($destination, 0755, true) && ! is_dir($destination)) {
            throw new RuntimeException("Unable to create upload directory [{$directory}].");
        }

        $extension = strtolower((string) ($file->guessExtension() ?: $file->getClientOriginalExtension() ?: 'jpg'));
        if (! in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            $extension = 'jpg';
        }

        $filename = $prefix.bin2hex(random_bytes(16)).'.'.$extension;
        $file->move($destination, $filename);

        return $filename;
    }

    /**
     * Delete a file from a public directory using only the basename.
     */
    public static function deleteFromPublic(string $directory, ?string $storedPath): void
    {
        if (! $storedPath) {
            return;
        }

        $filename = basename($storedPath);
        if ($filename === '' || $filename === '.' || $filename === '..') {
            return;
        }

        $path = public_path(trim($directory, '/\\').DIRECTORY_SEPARATOR.$filename);
        if (is_file($path)) {
            @unlink($path);
        }
    }
}
