<?php

declare(strict_types=1);

namespace App\Application\Auth;

use App\Domain\Entity\User;

interface PasswordResetNotifierInterface
{
    public function notify(User $user, string $plainToken, \DateTimeImmutable $expiresAt): void;
}
