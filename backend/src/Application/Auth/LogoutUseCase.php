<?php

declare(strict_types=1);

namespace App\Application\Auth;

use App\Domain\Repository\RefreshTokenRepositoryInterface;
use App\DTO\Auth\LogoutRequest;

final class LogoutUseCase
{
    public function __construct(private readonly RefreshTokenRepositoryInterface $refreshTokens)
    {
    }

    public function execute(LogoutRequest $request): void
    {
        $refreshToken = $this->refreshTokens->findActiveByHash(RefreshTokenManager::hashToken($request->refreshToken));

        if ($refreshToken === null) {
            return;
        }

        $refreshToken->revoke();
        $this->refreshTokens->save($refreshToken);
    }
}
