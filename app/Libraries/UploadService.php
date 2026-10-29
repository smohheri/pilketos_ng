<?php

namespace App\Libraries;

use CodeIgniter\HTTP\Files\UploadedFile;

class UploadService
{
    public const MAX_BYTES = 2 * 1024 * 1024;

    /**
     * @return array{ok: bool, path?: string, message?: string}
     */
    public function image(UploadedFile $file, string $folder): array
    {
        if (! $file->isValid() || $file->hasMoved()) {
            return ['ok' => false, 'message' => 'Berkas gambar tidak valid.'];
        }

        if ($file->getSize() > self::MAX_BYTES) {
            return ['ok' => false, 'message' => 'Ukuran gambar maksimal 2 MB.'];
        }

        $extension = strtolower($file->getClientExtension() ?: $file->guessExtension() ?: '');
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (! in_array($extension, $allowed, true)) {
            return ['ok' => false, 'message' => 'Format gambar harus JPG, PNG, atau WEBP.'];
        }

        $mime = strtolower((string) $file->getMimeType());
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
        if (! in_array($mime, $allowedMimes, true)) {
            return ['ok' => false, 'message' => 'Tipe MIME gambar tidak diizinkan.'];
        }

        $info = @getimagesize($file->getTempName());
        if ($info === false) {
            return ['ok' => false, 'message' => 'Isi berkas bukan gambar yang valid.'];
        }

        $folder = trim($folder, '/');
        $destination = FCPATH . 'uploads/' . $folder;
        if (! is_dir($destination) && ! mkdir($destination, 0755, true) && ! is_dir($destination)) {
            return ['ok' => false, 'message' => 'Folder unggahan tidak dapat dibuat.'];
        }

        $filename = $folder . '-' . date('Ymd') . '-' . bin2hex(random_bytes(6)) . '.' . $extension;
        $file->move($destination, $filename, true);

        return ['ok' => true, 'path' => 'uploads/' . $folder . '/' . $filename];
    }

    public function delete(?string $relativePath): void
    {
        if ($relativePath === null || $relativePath === '') {
            return;
        }

        $base = realpath(FCPATH . 'uploads');
        $path = realpath(FCPATH . ltrim($relativePath, '/'));

        if ($base !== false && $path !== false && str_starts_with($path, $base) && is_file($path)) {
            @unlink($path);
        }
    }
}