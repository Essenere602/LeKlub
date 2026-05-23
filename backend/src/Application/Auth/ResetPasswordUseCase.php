<?php

declare(strict_types=1);

namespace App\Application\Auth;

use App\Domain\Repository\PasswordResetTokenRepositoryInterface;
use App\Domain\Repository\RefreshTokenRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\DTO\Auth\ResetPasswordRequest;
use DomainException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class ResetPasswordUseCase
{
    public function __construct(
        private readonly PasswordResetTokenRepositoryInterface $tokens,
        private readonly UserRepositoryInterface $users,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly RefreshTokenRepositoryInterface $refreshTokens,
    ) {
    }

    public function execute(ResetPasswordRequest $request): void
    {
        $token = $this->tokens->findActiveByHash(RequestPasswordResetUseCase::hashToken($request->token));

        if ($token === null || !$token->isUsable()) {
            throw new DomainException('PASSWORD_RESET_FAILED');
        }

        $user = $token->getUser();
        $user->setPassword($this->passwordHasher->hashPassword($user, $request->newPassword));
        $token->markAsUsed();
        $this->refreshTokens->revokeAllForUser($user);

        $this->tokens->save($token);
        $this->users->save($user);
    }
}
