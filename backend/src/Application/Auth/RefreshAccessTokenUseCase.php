<?php

declare(strict_types=1);

namespace App\Application\Auth;

use App\Domain\Repository\RefreshTokenRepositoryInterface;
use App\DTO\Auth\RefreshTokenRequest;
use DomainException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

final class RefreshAccessTokenUseCase
{
    public function __construct(
        private readonly RefreshTokenRepositoryInterface $refreshTokens,
        private readonly RefreshTokenManager $refreshTokenManager,
        private readonly JWTTokenManagerInterface $jwtManager,
    ) {
    }

    /**
     * @return array{token: string, refreshToken: string}
     */
    public function execute(RefreshTokenRequest $request): array
    {
        $refreshToken = $this->refreshTokens->findActiveByHash(RefreshTokenManager::hashToken($request->refreshToken));

        if ($refreshToken === null || !$refreshToken->isUsable()) {
            throw new DomainException('REFRESH_TOKEN_INVALID');
        }

        $user = $refreshToken->getUser();
        $refreshToken->markAsUsed();
        $refreshToken->revoke();
        $this->refreshTokens->save($refreshToken);

        return [
            'token' => $this->jwtManager->create($user),
            'refreshToken' => $this->refreshTokenManager->createForUser($user),
        ];
    }
}
