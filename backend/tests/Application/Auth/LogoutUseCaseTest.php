<?php

declare(strict_types=1);

namespace App\Tests\Application\Auth;

use App\Application\Auth\LogoutUseCase;
use App\Application\Auth\RefreshTokenManager;
use App\Domain\Entity\RefreshToken;
use App\Domain\Entity\User;
use App\Domain\Repository\RefreshTokenRepositoryInterface;
use App\DTO\Auth\LogoutRequest;
use PHPUnit\Framework\TestCase;

final class LogoutUseCaseTest extends TestCase
{
    public function testItRevokesCurrentRefreshToken(): void
    {
        $plainToken = str_repeat('b', 64);
        $refreshToken = new RefreshToken(
            new User('user@example.test', 'samuel', 'hash'),
            RefreshTokenManager::hashToken($plainToken),
            new \DateTimeImmutable('+30 days')
        );
        $repository = new LogoutRefreshTokenRepository($refreshToken);
        $useCase = new LogoutUseCase($repository);

        $useCase->execute(LogoutRequest::fromArray(['refreshToken' => $plainToken]));

        self::assertNotNull($refreshToken->getRevokedAt());
        self::assertSame($refreshToken, $repository->savedToken);
    }

    public function testItIgnoresUnknownRefreshToken(): void
    {
        $repository = new LogoutRefreshTokenRepository();
        $useCase = new LogoutUseCase($repository);

        $useCase->execute(LogoutRequest::fromArray(['refreshToken' => str_repeat('b', 64)]));

        self::assertNull($repository->savedToken);
    }
}

final class LogoutRefreshTokenRepository implements RefreshTokenRepositoryInterface
{
    public ?RefreshToken $savedToken = null;

    public function __construct(private readonly ?RefreshToken $refreshToken = null)
    {
    }

    public function save(RefreshToken $refreshToken): void
    {
        $this->savedToken = $refreshToken;
    }

    public function findActiveByHash(string $tokenHash): ?RefreshToken
    {
        if ($this->refreshToken === null || $this->refreshToken->getTokenHash() !== $tokenHash) {
            return null;
        }

        return $this->refreshToken;
    }

    public function revokeAllForUser(User $user): void
    {
    }
}
