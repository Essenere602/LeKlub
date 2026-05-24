<?php

declare(strict_types=1);

namespace App\Application\User;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface AvatarStorageInterface
{
    public function store(UploadedFile $file, string $publicBaseUrl, ?string $previousAvatarUrl): string;
}
