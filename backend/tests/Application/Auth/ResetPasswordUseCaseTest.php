<?php

declare(strict_types=1);

namespace App\Tests\Application\Auth;

use App\Application\Auth\RequestPasswordResetUseCase;
use App\Application\Auth\ResetPasswordUseCase;
use App\Domain\Entity\PasswordResetToken;
use App\Domain\Entity\RefreshToken;
use App\Domain\Entity\User;
use App\Domain\Repository\PasswordResetTokenRepositoryInterface;
use App\Domain\Repository\RefreshTokenRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\DTO\Auth\ResetPasswordRequest;
use DomainException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

final class ResetPasswordUseCaseTest extends TestCase
{
    public function testItResetsPasswordWithValidToken(): void
    {
        $user = new User('user@example.test', 'samuel', 'old_hash');
        $plainToken = str_repeat('a', 64);
        $token = new PasswordResetToken(
            $user,
            RequestPasswordResetUseCase::hashToken($plainToken),
            new \DateTimeImmutable('+30 minutes')
        );
        $tokens = new ResetTokenRepository($token);
        $users = new ResetUserRepository();
        $refreshTokens = new ResetRefreshTokenRepository();
        $useCase = new ResetPasswordUseCase($tokens, $users, new ResetPasswordHasher(), $refreshTokens);

        $useCase->execute(ResetPasswordRequest::fromArray([
            'token' => $plainToken,
            'newPassword' => 'NewPassword123',
            'newPasswordConfirmation' => 'NewPassword123',
        ]));

        self::assertSame('hashed_NewPassword123', $user->getPassword());
        self::assertNotNull($token->getUsedAt());
        self::assertSame($token, $tokens->savedToken);
        self::assertSame($user, $users->savedUser);
        self::assertSame($user, $refreshTokens->revokedUser);
    }

    public function testItRejectsUnknownToken(): void
    {
        $useCase = new ResetPasswordUseCase(new ResetTokenRepository(), new ResetUserRepository(), new ResetPasswordHasher(), new ResetRefreshTokenRepository());

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('PASSWORD_RESET_FAILED');

        $useCase->execute(ResetPasswordRequest::fromArray([
            'token' => str_repeat('a', 64),
            'newPassword' => 'NewPassword123',
            'newPasswordConfirmation' => 'NewPassword123',
        ]));
    }
}

final class ResetRefreshTokenRepository implements RefreshTokenRepositoryInterface
{
    public ?User $revokedUser = null;

    public function save(RefreshToken $refreshToken): void
    {
    }

    public function findActiveByHash(string $tokenHash): ?RefreshToken
    {
        return null;
    }

    public function revokeAllForUser(User $user): void
    {
        $this->revokedUser = $user;
    }
}

final class ResetPasswordHasher implements UserPasswordHasherInterface
{
    public function hashPassword(PasswordAuthenticatedUserInterface $user, #[\SensitiveParameter] string $plainPassword): string
    {
        return 'hashed_'.$plainPassword;
    }

    public function isPasswordValid(PasswordAuthenticatedUserInterface $user, #[\SensitiveParameter] string $plainPassword): bool
    {
        return true;
    }

    public function needsRehash(PasswordAuthenticatedUserInterface $user): bool
    {
        return false;
    }
}

final class ResetTokenRepository implements PasswordResetTokenRepositoryInterface
{
    public ?PasswordResetToken $savedToken = null;

    public function __construct(private readonly ?PasswordResetToken $token = null)
    {
    }

    public function save(PasswordResetToken $token): void
    {
        $this->savedToken = $token;
    }

    public function findActiveByHash(string $tokenHash): ?PasswordResetToken
    {
        if ($this->token === null || $this->token->getTokenHash() !== $tokenHash) {
            return null;
        }

        return $this->token;
    }

    public function invalidateActiveForUser(User $user): void
    {
    }
}

final class ResetUserRepository implements UserRepositoryInterface
{
    public ?User $savedUser = null;

    public function findOneByEmail(string $email): ?User
    {
        return null;
    }

    public function findOneByUsername(string $username): ?User
    {
        return null;
    }

    public function findById(int $id): ?User
    {
        return null;
    }

    public function findForDirectory(User $currentUser, ?string $query, int $limit): array
    {
        return [];
    }

    public function paginateForAdmin(?string $query, int $page, int $limit): array
    {
        return [];
    }

    public function countForAdmin(?string $query): int
    {
        return 0;
    }

    public function save(User $user): void
    {
        $this->savedUser = $user;
    }
}
