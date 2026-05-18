<?php

declare(strict_types=1);

namespace App\Tests\Application\User;

use App\Application\User\ListUsersUseCase;
use App\Domain\Entity\User;
use App\Domain\Entity\UserProfile;
use App\Domain\Repository\UserRepositoryInterface;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

final class ListUsersUseCaseTest extends TestCase
{
    public function testItReturnsOnlySafeUserDirectoryFields(): void
    {
        $currentUser = self::userWithId(1, 'samuel@example.com', 'samuel');
        $otherUser = self::userWithId(2, 'alex@example.com', 'alex');
        $profile = new UserProfile($otherUser);
        $profile->update('Alex Paris', null, null, 'https://example.com/avatar.png');

        $repository = new DirectoryUserRepository([$otherUser]);
        $useCase = new ListUsersUseCase($repository);

        $result = $useCase->execute($currentUser, ' alex ', 50);

        self::assertSame('alex', $repository->query);
        self::assertSame(20, $repository->limit);
        self::assertSame([
            [
                'id' => 2,
                'username' => 'alex',
                'displayName' => 'Alex Paris',
                'avatarUrl' => 'https://example.com/avatar.png',
            ],
        ], $result);
        self::assertArrayNotHasKey('email', $result[0]);
        self::assertArrayNotHasKey('roles', $result[0]);
        self::assertArrayNotHasKey('password', $result[0]);
    }

    private static function userWithId(int $id, string $email, string $username): User
    {
        $user = new User($email, $username, 'hash');
        $property = new ReflectionProperty(User::class, 'id');
        $property->setValue($user, $id);

        return $user;
    }
}

final class DirectoryUserRepository implements UserRepositoryInterface
{
    public ?string $query = null;
    public ?int $limit = null;

    /**
     * @param list<User> $users
     */
    public function __construct(private readonly array $users)
    {
    }

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
        $this->query = $query;
        $this->limit = $limit;

        return $this->users;
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
    }
}
