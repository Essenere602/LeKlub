<?php

declare(strict_types=1);

namespace App\Tests\Application\Auth;

use App\Application\Auth\PasswordResetNotifierInterface;
use App\Application\Auth\RequestPasswordResetUseCase;
use App\Domain\Entity\PasswordResetToken;
use App\Domain\Entity\User;
use App\Domain\Repository\PasswordResetTokenRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\DTO\Auth\ForgotPasswordRequest;
use PHPUnit\Framework\TestCase;

final class RequestPasswordResetUseCaseTest extends TestCase
{
    public function testItCreatesHashedTokenForExistingUser(): void
    {
        $user = new User('user@example.test', 'samuel', 'hash');
        $users = new PasswordResetUserRepository($user);
        $tokens = new PasswordResetTokenRepository();
        $notifier = new PasswordResetNotifier();
        $useCase = new RequestPasswordResetUseCase($users, $tokens, $notifier);

        $useCase->execute(ForgotPasswordRequest::fromArray(['email' => 'user@example.test']));

        self::assertTrue($tokens->invalidated);
        self::assertInstanceOf(PasswordResetToken::class, $tokens->savedToken);
        self::assertSame($user, $tokens->savedToken->getUser());
        self::assertNotSame($notifier->plainToken, $tokens->savedToken->getTokenHash());
        self::assertSame(hash('sha256', (string) $notifier->plainToken), $tokens->savedToken->getTokenHash());
        self::assertSame($user, $notifier->user);
    }

    public function testItDoesNothingForUnknownEmail(): void
    {
        $tokens = new PasswordResetTokenRepository();
        $notifier = new PasswordResetNotifier();
        $useCase = new RequestPasswordResetUseCase(new PasswordResetUserRepository(), $tokens, $notifier);

        $useCase->execute(ForgotPasswordRequest::fromArray(['email' => 'unknown@example.test']));

        self::assertFalse($tokens->invalidated);
        self::assertNull($tokens->savedToken);
        self::assertNull($notifier->plainToken);
    }
}

final class PasswordResetNotifier implements PasswordResetNotifierInterface
{
    public ?User $user = null;
    public ?string $plainToken = null;

    public function notify(User $user, string $plainToken, \DateTimeImmutable $expiresAt): void
    {
        $this->user = $user;
        $this->plainToken = $plainToken;
    }
}

final class PasswordResetUserRepository implements UserRepositoryInterface
{
    public ?User $savedUser = null;

    public function __construct(private readonly ?User $user = null)
    {
    }

    public function findOneByEmail(string $email): ?User
    {
        return $this->user;
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

final class PasswordResetTokenRepository implements PasswordResetTokenRepositoryInterface
{
    public ?PasswordResetToken $savedToken = null;
    public bool $invalidated = false;

    public function save(PasswordResetToken $token): void
    {
        $this->savedToken = $token;
    }

    public function findActiveByHash(string $tokenHash): ?PasswordResetToken
    {
        return null;
    }

    public function invalidateActiveForUser(User $user): void
    {
        $this->invalidated = true;
    }
}
