<?php

declare(strict_types=1);

namespace App\Tests\Application\Auth;

use App\Application\Auth\RefreshAccessTokenUseCase;
use App\Application\Auth\RefreshTokenManager;
use App\Domain\Entity\RefreshToken;
use App\Domain\Entity\User;
use App\Domain\Repository\RefreshTokenRepositoryInterface;
use App\DTO\Auth\RefreshTokenRequest;
use DomainException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class RefreshAccessTokenUseCaseTest extends TestCase
{
    public function testItRotatesRefreshTokenAndReturnsNewTokens(): void
    {
        $user = new User('user@example.test', 'samuel', 'hash');
        $plainToken = str_repeat('a', 64);
        $refreshToken = new RefreshToken(
            $user,
            RefreshTokenManager::hashToken($plainToken),
            new \DateTimeImmutable('+30 days')
        );
        $repository = new RefreshAccessTokenRepository($refreshToken);
        $useCase = new RefreshAccessTokenUseCase(
            $repository,
            new RefreshTokenManager($repository),
            new FakeJwtTokenManager()
        );

        $result = $useCase->execute(RefreshTokenRequest::fromArray(['refreshToken' => $plainToken]));

        self::assertSame('jwt_for_user@example.test', $result['token']);
        self::assertNotSame($plainToken, $result['refreshToken']);
        self::assertNotNull($refreshToken->getRevokedAt());
        self::assertNotNull($refreshToken->getLastUsedAt());
        self::assertCount(2, $repository->savedTokens);
    }

    public function testItRejectsInvalidRefreshToken(): void
    {
        $useCase = new RefreshAccessTokenUseCase(
            new RefreshAccessTokenRepository(),
            new RefreshTokenManager(new RefreshAccessTokenRepository()),
            new FakeJwtTokenManager()
        );

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('REFRESH_TOKEN_INVALID');

        $useCase->execute(RefreshTokenRequest::fromArray(['refreshToken' => str_repeat('a', 64)]));
    }
}

final class RefreshAccessTokenRepository implements RefreshTokenRepositoryInterface
{
    /**
     * @var list<RefreshToken>
     */
    public array $savedTokens = [];

    public function __construct(private readonly ?RefreshToken $refreshToken = null)
    {
    }

    public function save(RefreshToken $refreshToken): void
    {
        $this->savedTokens[] = $refreshToken;
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

final class FakeJwtTokenManager implements JWTTokenManagerInterface
{
    public function create(UserInterface $user): string
    {
        return 'jwt_for_'.$user->getUserIdentifier();
    }

    public function createFromPayload(UserInterface $user, array $payload = []): string
    {
        return $this->create($user);
    }

    public function decode(TokenInterface $token): array|bool
    {
        return [];
    }

    public function parse(string $token): array
    {
        return [];
    }

    public function getUserIdClaim(): string
    {
        return 'email';
    }
}
