<?php
declare(strict_types=1);
namespace App\Core;

class Upload {
    private array $allowedExtensions = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'zip', 'txt', 'csv'];
    private array $allowedMimes = [
        'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'image/jpeg', 'image/png', 'application/zip', 'text/plain', 'text/csv',
    ];
    private int $maxSize;

    public function __construct(int $maxSize = 10485760) { $this->maxSize = $maxSize; }

    public function store(array $file, string $subdir): array {
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return ['ok' => false, 'error' => 'No file uploaded.'];
        }
        if ($file['error'] !== UPLOAD_ERR_OK) return ['ok' => false, 'error' => 'Upload error.'];
        if ($file['size'] > $this->maxSize) return ['ok' => false, 'error' => 'File too large.'];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION) ?: '');
        if ($extension === '' || !in_array($extension, $this->allowedExtensions, true)) {
            return ['ok' => false, 'error' => 'File type not allowed.'];
        }
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        if (!in_array($mime, $this->allowedMimes, true)) {
            return ['ok' => false, 'error' => 'Detected file type not allowed.'];
        }
        $storageDir = APP_ROOT . '/storage/private_uploads/' . $subdir;
        if (!is_dir($storageDir)) @mkdir($storageDir, 0770, true);
        $storedName = bin2hex(random_bytes(16)) . '.' . $extension;
        $target = $storageDir . '/' . $storedName;
        if (!move_uploaded_file($file['tmp_name'], $target)) {
            return ['ok' => false, 'error' => 'Failed to store file.'];
        }
        @chmod($target, 0660);
        return ['ok' => true, 'path' => 'private_uploads/' . $subdir . '/' . $storedName,
            'original_name' => $file['name'], 'mime' => $mime, 'size' => (int) $file['size']];
    }
    public static function fullPath(string $relativePath): string {
        return APP_ROOT . '/storage/' . $relativePath;
    }
}
