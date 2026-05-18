<?php

declare(strict_types=1);

namespace App\Tests\Application\Admin;

use App\Application\Admin\AdminPresenter;
use App\Application\Admin\ListAdminUsersUseCase;
use App\Domain\Entity\Post;
use App\Domain\Entity\User;
use App\Domain\Entity\UserProfile;
use App\Domain\Repository\PostRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\PostReactionType;
use App\Shared\Api\Pagination;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

final class ListAdminUsersUseCaseTest extends TestCase
{
    public function testItReturnsOnlySafeAdminUserFields(): void
    {
        $user = self::userWithId(7, 'alex@example.com', 'alex');
        $profile = new UserProfile($user);
        $profile->update('Alex Paris', null, null, 'https://example.com/avatar.png');

        $repository = new AdminUserRepository([$user], 1);
        $useCase = new ListAdminUsersUseCase($repository, new AdminPresenter(new EmptyAdminPostRepository()));

        $result = $useCase->execute(new Pagination(1, 20), ' alex ');

        self::assertSame('alex', $repository->query);
        self::assertSame(1, $repository->page);
        self::assertSame(20, $repository->limit);
        self::assertSame(1, $result['pagination']['total']);
        self::assertSame('alex', $result['users'][0]['username']);
        self::assertSame('Alex Paris', $result['users'][0]['displayName']);
        self::assertSame(['ROLE_USER'], $result['users'][0]['roles']);
        self::assertArrayNotHasKey('email', $result['users'][0]);
        self::assertArrayNotHasKey('password', $result['users'][0]);
    }

    private static function userWithId(int $id, string $email, string $username): User
    {
        $user = new User($email, $username, 'hash');
        $property = new ReflectionProperty(User::class, 'id');
        $property->setValue($user, $id);

        return $user;
    }
}

final class AdminUserRepository implements UserRepositoryInterface
{
    public ?string $query = null;
    public ?int $page = null;
    public ?int $limit = null;

    /**
     * @param list<User> $users
     */
    public function __construct(private readonly array $users, private readonly int $total)
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
        return [];
    }

    public function paginateForAdmin(?string $query, int $page, int $limit): array
    {
        $this->query = $query;
        $this->page = $page;
        $this->limit = $limit;

        return $this->users;
    }

    public function countForAdmin(?string $query): int
    {
        return $this->total;
    }

    public function save(User $user): void
    {
    }
}

final class EmptyAdminPostRepository implements PostRepositoryInterface
{
    public function save(Post $post): void
    {
    }

    public function findVisibleById(int $id): ?Post
    {
        return null;
    }

    public function paginateVisible(int $page, int $limit): array
    {
        return [];
    }

    public function countVisible(): int
    {
        return 0;
    }

    public function paginateVisibleForAdmin(int $page, int $limit): array
    {
        return [];
    }

    public function countVisibleComments(Post $post): int
    {
        return 0;
    }

    public function countReactions(Post $post, PostReactionType $type): int
    {
        return 0;
    }
}
