<?php

declare(strict_types=1);

namespace App\Tests\Application\User;

use App\Application\User\GetCurrentUserProfileUseCase;
use App\Application\User\UpdateCurrentUserAccountUseCase;
use App\Domain\Entity\User;
use App\Domain\Exception\DuplicateUserException;
use App\Domain\Repository\UserRepositoryInterface;
use App\DTO\User\UpdateAccountRequest;
use DomainException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

final class UpdateCurrentUserAccountUseCaseTest extends TestCase
{
    public function testItUpdatesUsernameWithoutCurrentPassword(): void
    {
        $user = new User('samuel@example.com', 'samuel', 'hash');
        $repository = new AccountUserRepository();
        $useCase = self::useCase($repository);

        $result = $useCase->execute($user, UpdateAccountRequest::fromArray([
            'username' => 'samuel_60',
        ]));

        self::assertSame($user, $repository->savedUser);
        self::assertSame('samuel_60', $user->getUsername());
        self::assertSame('samuel_60', $result['username']);
    }

    public function testItUpdatesEmailWithValidCurrentPassword(): void
    {
        $user = new User('samuel@example.com', 'samuel', 'hash');
        $repository = new AccountUserRepository();
        $passwordHasher = new AccountPasswordHasher(true);
        $useCase = self::useCase($repository, $passwordHasher);

        $result = $useCase->execute($user, UpdateAccountRequest::fromArray([
            'email' => 'new@example.com',
            'currentPassword' => 'CurrentPassword123',
        ]));

        self::assertSame($user, $repository->savedUser);
        self::assertSame('new@example.com', $user->getEmail());
        self::assertSame('new@example.com', $result['email']);
        self::assertSame('CurrentPassword123', $passwordHasher->validatedPassword);
    }

    public function testItRejectsEmailUpdateWithInvalidCurrentPassword(): void
    {
        $user = new User('samuel@example.com', 'samuel', 'hash');
        $repository = new AccountUserRepository();
        $useCase = self::useCase($repository, new AccountPasswordHasher(false));

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('ACCOUNT_UPDATE_FAILED');

        try {
            $useCase->execute($user, UpdateAccountRequest::fromArray([
                'email' => 'new@example.com',
                'currentPassword' => 'wrong',
            ]));
        } finally {
            self::assertNull($repository->savedUser);
            self::assertSame('samuel@example.com', $user->getEmail());
        }
    }

    public function testItRejectsDuplicateEmail(): void
    {
        $user = new User('samuel@example.com', 'samuel', 'hash');
        $otherUser = new User('other@example.com', 'other', 'hash');
        $repository = new AccountUserRepository(emailUser: $otherUser);
        $useCase = self::useCase($repository, new AccountPasswordHasher(true));

        $this->expectException(DuplicateUserException::class);
        $this->expectExceptionMessage('EMAIL_ALREADY_USED');

        $useCase->execute($user, UpdateAccountRequest::fromArray([
            'email' => 'other@example.com',
            'currentPassword' => 'CurrentPassword123',
        ]));
    }

    public function testItRejectsDuplicateUsername(): void
    {
        $user = new User('samuel@example.com', 'samuel', 'hash');
        $otherUser = new User('other@example.com', 'other', 'hash');
        $repository = new AccountUserRepository(usernameUser: $otherUser);
        $useCase = self::useCase($repository);

        $this->expectException(DuplicateUserException::class);
        $this->expectExceptionMessage('USERNAME_ALREADY_USED');

        $useCase->execute($user, UpdateAccountRequest::fromArray([
            'username' => 'other',
        ]));
    }

    private static function useCase(
        ?AccountUserRepository $repository = null,
        ?AccountPasswordHasher $passwordHasher = null,
    ): UpdateCurrentUserAccountUseCase {
        return new UpdateCurrentUserAccountUseCase(
            $repository ?? new AccountUserRepository(),
            $passwordHasher ?? new AccountPasswordHasher(true),
            new GetCurrentUserProfileUseCase(),
        );
    }
}

final class AccountPasswordHasher implements UserPasswordHasherInterface
{
    public ?string $validatedPassword = null;

    public function __construct(private readonly bool $isPasswordValid)
    {
    }

    public function hashPassword(PasswordAuthenticatedUserInterface $user, #[\SensitiveParameter] string $plainPassword): string
    {
        return 'hashed_'.$plainPassword;
    }

    public function isPasswordValid(PasswordAuthenticatedUserInterface $user, #[\SensitiveParameter] string $plainPassword): bool
    {
        $this->validatedPassword = $plainPassword;

        return $this->isPasswordValid;
    }

    public function needsRehash(PasswordAuthenticatedUserInterface $user): bool
    {
        return false;
    }
}

final class AccountUserRepository implements UserRepositoryInterface
{
    public ?User $savedUser = null;

    public function __construct(
        private readonly ?User $emailUser = null,
        private readonly ?User $usernameUser = null,
    ) {
    }

    public function findOneByEmail(string $email): ?User
    {
        return $this->emailUser;
    }

    public function findOneByUsername(string $username): ?User
    {
        return $this->usernameUser;
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
