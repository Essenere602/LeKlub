<?php

declare(strict_types=1);

namespace App\Application\Auth;

use App\Domain\Entity\RefreshToken;
use App\Domain\Entity\User;
use App\Domain\Repository\RefreshTokenRepositoryInterface;
use DateInterval;
use DateTimeImmutable;

final class RefreshTokenManager
{
    private const TOKEN_TTL = 'P30D';

    public function __construct(private readonly RefreshTokenRepositoryInterface $refreshTokens)
    {
    }

    public function createForUser(User $user): string
    {
        $plainToken = bin2hex(random_bytes(32));
        $expiresAt = (new DateTimeImmutable())->add(new DateInterval(self::TOKEN_TTL));

        $this->refreshTokens->save(new RefreshToken($user, self::hashToken($plainToken), $expiresAt));

        return $plainToken;
    }

    public static function hashToken(string $plainToken): string
    {
        return hash('sha256', $plainToken);
    }
}
