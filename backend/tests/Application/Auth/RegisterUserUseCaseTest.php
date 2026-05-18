<?php

declare(strict_types=1);

namespace App\Tests\Application\Auth;

use App\Application\Auth\RegisterUserUseCase;
use App\Domain\Entity\User;
use App\Domain\Exception\DuplicateUserException;
use App\Domain\Repository\UserRepositoryInterface;
use App\DTO\Auth\RegisterRequest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

final class RegisterUserUseCaseTest extends TestCase
{
    public function testItRegistersUserWithProfile(): void
    {
        $repository = new InMemoryUserRepository();
        $useCase = new RegisterUserUseCase($repository, new PlainTestPasswordHasher());

        $user = $useCase->execute(RegisterRequest::fromArray([
            'email' => 'USER@example.com',
            'username' => 'samuel',
            'password' => 'Password123!',
        ]));

        self::assertSame('user@example.com', $user->getEmail());
        self::assertSame('samuel', $user->getUsername());
        self::assertSame('hashed-Password123!', $user->getPassword());
        self::assertContains('ROLE_USER', $user->getRoles());
        self::assertNotNull($user->getProfile());
        self::assertSame($user, $repository->findOneByEmail('user@example.com'));
    }

    public function testItRejectsDuplicateEmail(): void
    {
        $repository = new InMemoryUserRepository();
        $repository->save(new User('user@example.com', 'existing', 'hash'));
        $useCase = new RegisterUserUseCase($repository, new PlainTestPasswordHasher());

        $this->expectException(DuplicateUserException::class);
        $this->expectExceptionMessage('EMAIL_ALREADY_USED');

        $useCase->execute(RegisterRequest::fromArray([
            'email' => 'USER@example.com',
            'username' => 'samuel',
            'password' => 'Password123!',
        ]));
    }

    public function testItRejectsDuplicateUsername(): void
    {
        $repository = new InMemoryUserRepository();
        $repository->save(new User('other@example.com', 'samuel', 'hash'));
        $useCase = new RegisterUserUseCase($repository, new PlainTestPasswordHasher());

        $this->expectException(DuplicateUserException::class);
        $this->expectExceptionMessage('USERNAME_ALREADY_USED');

        $useCase->execute(RegisterRequest::fromArray([
            'email' => 'user@example.com',
            'username' => 'samuel',
            'password' => 'Password123!',
        ]));
    }
}

final class PlainTestPasswordHasher implements UserPasswordHasherInterface
{
    public function hashPassword(PasswordAuthenticatedUserInterface $user, string $plainPassword): string
    {
        return 'hashed-'.$plainPassword;
    }

    public function isPasswordValid(PasswordAuthenticatedUserInterface $user, string $plainPassword): bool
    {
        return $user->getPassword() === $this->hashPassword($user, $plainPassword);
    }

    public function needsRehash(PasswordAuthenticatedUserInterface $user): bool
    {
        return false;
    }
}

final class InMemoryUserRepository implements UserRepositoryInterface
{
    /**
     * @var array<string, User>
     */
    private array $usersByEmail = [];

    /**
     * @var array<string, User>
     */
    private array $usersByUsername = [];

    public function findOneByEmail(string $email): ?User
    {
        return $this->usersByEmail[mb_strtolower($email)] ?? null;
    }

    public function findOneByUsername(string $username): ?User
    {
        return $this->usersByUsername[$username] ?? null;
    }

    public function findById(int $id): ?User
    {
        foreach ($this->usersByEmail as $user) {
            if ($user->getId() === $id) {
                return $user;
            }
        }

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
        return count($this->usersByEmail);
    }

    public function save(User $user): void
    {
        $this->usersByEmail[$user->getEmail()] = $user;
        $this->usersByUsername[$user->getUsername()] = $user;
    }
}
