<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\PasswordResetToken;
use App\Domain\Entity\User;

interface PasswordResetTokenRepositoryInterface
{
    public function save(PasswordResetToken $token): void;

    public function findActiveByHash(string $tokenHash): ?PasswordResetToken;

    public function invalidateActiveForUser(User $user): void;
}
