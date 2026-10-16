<?php

namespace App\Services;

use RuntimeException;

class FileUploadService
{
    public static function uploadPhoto(array $file, string $subfolder): ?string
    {
        if (empty($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Upload gagal, silakan coba lagi.');
        }

        $maxSize = config('app.upload_max_size');
        if ($file['size'] > $maxSize) {
            throw new RuntimeException('Ukuran file maksimal 2MB.');
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowedMime = config('app.upload_allowed_mime');
        if (!in_array($mime, $allowedMime, true)) {
            throw new RuntimeException('Format file harus JPG, PNG, atau WEBP.');
        }

        $extMap = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $ext = $extMap[$mime];

        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
        $destinationDir = dirname(__DIR__, 2) . '/public/uploads/' . $subfolder;

        if (!is_dir($destinationDir)) {
            mkdir($destinationDir, 0755, true);
        }

        $destination = $destinationDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new RuntimeException('Gagal menyimpan file.');
        }

        return '/uploads/' . $subfolder . '/' . $filename;
    }

    public static function deletePhoto(?string $path): void
    {
        if (!$path) {
            return;
        }

        $fullPath = dirname(__DIR__, 2) . '/public' . $path;
        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }
}
