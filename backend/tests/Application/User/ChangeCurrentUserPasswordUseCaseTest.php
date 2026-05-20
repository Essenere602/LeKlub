<?php

declare(strict_types=1);

namespace App\Tests\Application\User;

use App\Application\User\ChangeCurrentUserPasswordUseCase;
use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;
use App\DTO\User\ChangePasswordRequest;
use DomainException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class ChangeCurrentUserPasswordUseCaseTest extends TestCase
{
    public function testItChangesCurrentUserPassword(): void
    {
        $user = new User('user@example.com', 'samuel', 'old_hash');
        $repository = new ChangePasswordUserRepository();
        $passwordHasher = new FakePasswordHasher(true);
        $useCase = new ChangeCurrentUserPasswordUseCase($repository, $passwordHasher);

        $useCase->execute($user, ChangePasswordRequest::fromArray([
            'currentPassword' => 'OldPassword123',
            'newPassword' => 'NewPassword123',
            'newPasswordConfirmation' => 'NewPassword123',
        ]));

        self::assertSame($user, $repository->savedUser);
        self::assertSame('hashed_NewPassword123', $user->getPassword());
        self::assertSame('OldPassword123', $passwordHasher->validatedPassword);
    }

    public function testItRejectsInvalidCurrentPassword(): void
    {
        $user = new User('user@example.com', 'samuel', 'old_hash');
        $repository = new ChangePasswordUserRepository();
        $useCase = new ChangeCurrentUserPasswordUseCase($repository, new FakePasswordHasher(false));

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('PASSWORD_CHANGE_FAILED');

        try {
            $useCase->execute($user, ChangePasswordRequest::fromArray([
                'currentPassword' => 'WrongPassword123',
                'newPassword' => 'NewPassword123',
                'newPasswordConfirmation' => 'NewPassword123',
            ]));
        } finally {
            self::assertNull($repository->savedUser);
            self::assertSame('old_hash', $user->getPassword());
        }
    }
}

final class FakePasswordHasher implements UserPasswordHasherInterface
{
    public ?string $validatedPassword = null;

    public function __construct(private readonly bool $isPasswordValid)
    {
    }

    public function hashPassword(\Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface $user, #[\SensitiveParameter] string $plainPassword): string
    {
        return 'hashed_'.$plainPassword;
    }

    public function isPasswordValid(\Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface $user, #[\SensitiveParameter] string $plainPassword): bool
    {
        $this->validatedPassword = $plainPassword;

        return $this->isPasswordValid;
    }

    public function needsRehash(\Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface $user): bool
    {
        return false;
    }
}

final class ChangePasswordUserRepository implements UserRepositoryInterface
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
