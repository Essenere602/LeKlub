<?php

declare(strict_types=1);

namespace App\Application\User;

use App\Domain\Entity\User;
use App\Domain\Repository\RefreshTokenRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\DTO\User\ChangePasswordRequest;
use DomainException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class ChangeCurrentUserPasswordUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly RefreshTokenRepositoryInterface $refreshTokens,
    ) {
    }

    public function execute(User $user, ChangePasswordRequest $request): void
    {
        if (!$this->passwordHasher->isPasswordValid($user, $request->currentPassword)) {
            throw new DomainException('PASSWORD_CHANGE_FAILED');
        }

        $user->setPassword($this->passwordHasher->hashPassword($user, $request->newPassword));
        $this->refreshTokens->revokeAllForUser($user);
        $this->users->save($user);
    }
}
