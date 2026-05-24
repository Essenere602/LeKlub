<?php

declare(strict_types=1);

namespace App\Infrastructure\Storage;

use App\Application\User\AvatarStorageInterface;
use DomainException;
use RuntimeException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class LocalAvatarStorage implements AvatarStorageInterface
{
    private const MAX_SIZE_BYTES = 2 * 1024 * 1024;

    private const ALLOWED_MIMES = [
        'image/jpeg' => ['jpg', 'jpeg'],
        'image/png' => ['png'],
        'image/webp' => ['webp'],
    ];

    public function __construct(
        private readonly string $uploadDirectory,
        private readonly string $publicPath,
    ) {
    }

    public function store(UploadedFile $file, string $publicBaseUrl, ?string $previousAvatarUrl): string
    {
        $extension = $this->validate($file);
        $this->ensureUploadDirectoryExists();

        $filename = bin2hex(random_bytes(16)).'.'.$extension;
        $file->move($this->uploadDirectory, $filename);

        $this->deletePreviousLocalAvatar($previousAvatarUrl);

        return rtrim($publicBaseUrl, '/').$this->publicPath.'/'.$filename;
    }

    private function validate(UploadedFile $file): string
    {
        if (!$file->isValid()) {
            throw new DomainException('AVATAR_UPLOAD_INVALID');
        }

        if ($file->getSize() === null || $file->getSize() > self::MAX_SIZE_BYTES) {
            throw new DomainException('AVATAR_UPLOAD_TOO_LARGE');
        }

        $mimeType = (string) $file->getMimeType();
        $extension = strtolower($file->getClientOriginalExtension());

        if (!isset(self::ALLOWED_MIMES[$mimeType]) || !in_array($extension, self::ALLOWED_MIMES[$mimeType], true)) {
            throw new DomainException('AVATAR_UPLOAD_UNSUPPORTED_TYPE');
        }

        return $extension === 'jpeg' ? 'jpg' : $extension;
    }

    private function ensureUploadDirectoryExists(): void
    {
        if (is_dir($this->uploadDirectory)) {
            return;
        }

        if (!mkdir($concurrentDirectory = $this->uploadDirectory, 0775, true) && !is_dir($concurrentDirectory)) {
            throw new RuntimeException('Unable to create avatar upload directory.');
        }
    }

    private function deletePreviousLocalAvatar(?string $previousAvatarUrl): void
    {
        if ($previousAvatarUrl === null || trim($previousAvatarUrl) === '') {
            return;
        }

        $path = parse_url($previousAvatarUrl, PHP_URL_PATH);

        if (!is_string($path) || !str_starts_with($path, $this->publicPath.'/')) {
            return;
        }

        $filename = basename($path);
        $filePath = $this->uploadDirectory.'/'.$filename;

        if (is_file($filePath)) {
            @unlink($filePath);
        }
    }
}
