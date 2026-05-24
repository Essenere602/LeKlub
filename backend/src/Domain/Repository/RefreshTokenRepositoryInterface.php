<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\RefreshToken;
use App\Domain\Entity\User;

interface RefreshTokenRepositoryInterface
{
    public function save(RefreshToken $refreshToken): void;

    public function findActiveByHash(string $tokenHash): ?RefreshToken;

    public function revokeAllForUser(User $user): void;
}
