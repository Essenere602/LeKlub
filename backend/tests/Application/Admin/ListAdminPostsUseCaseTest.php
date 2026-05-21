<?php

declare(strict_types=1);

namespace App\Tests\Application\Admin;

use App\Application\Admin\AdminPresenter;
use App\Application\Admin\ListAdminPostsUseCase;
use App\Domain\Entity\Post;
use App\Domain\Entity\User;
use App\Domain\Entity\UserWarning;
use App\Domain\Repository\AdminPostModerationRepositoryInterface;
use App\Domain\Repository\PostRepositoryInterface;
use App\Domain\Repository\UserWarningRepositoryInterface;
use App\Domain\ValueObject\AdminContentStatus;
use App\Domain\ValueObject\PostReactionType;
use App\Shared\Api\Pagination;
use PHPUnit\Framework\TestCase;

final class ListAdminPostsUseCaseTest extends TestCase
{
    public function testItListsDeletedPostsWithModerationMetadata(): void
    {
        $admin = new User('admin@example.com', 'admin', 'hash');
        $post = new Post(new User('author@example.com', 'author', 'hash'), 'Moderated content');
        $post->delete($admin);
        $moderationRepository = new ModerationPostRepository([$post], 1);

        $useCase = new ListAdminPostsUseCase(
            $moderationRepository,
            new AdminPresenter(new CountingPostRepository(), new EmptyWarningRepository())
        );

        $result = $useCase->execute(new Pagination(2, 10), AdminContentStatus::Deleted, 'author');

        self::assertSame(AdminContentStatus::Deleted, $moderationRepository->status);
        self::assertSame('author', $moderationRepository->query);
        self::assertSame(2, $moderationRepository->page);
        self::assertSame(10, $moderationRepository->limit);
        self::assertSame(1, $result['pagination']['total']);
        self::assertSame('Moderated content', $result['posts'][0]['content']);
        self::assertNotNull($result['posts'][0]['deletedAt']);
        self::assertSame('admin', $result['posts'][0]['deletedBy']['username']);
        self::assertArrayNotHasKey('email', $result['posts'][0]['author']);
    }
}

final class ModerationPostRepository implements AdminPostModerationRepositoryInterface
{
    public ?AdminContentStatus $status = null;
    public ?string $query = null;
    public ?int $page = null;
    public ?int $limit = null;

    /**
     * @param list<Post> $posts
     */
    public function __construct(private readonly array $posts, private readonly int $total)
    {
    }

    public function paginateForModeration(AdminContentStatus $status, ?string $query, int $page, int $limit): array
    {
        $this->status = $status;
        $this->query = $query;
        $this->page = $page;
        $this->limit = $limit;

        return $this->posts;
    }

    public function countForModeration(AdminContentStatus $status, ?string $query): int
    {
        return $this->total;
    }
}

final class CountingPostRepository implements PostRepositoryInterface
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

final class EmptyWarningRepository implements UserWarningRepositoryInterface
{
    public function save(UserWarning $warning): void
    {
    }

    public function countForUser(User $user): int
    {
        return 0;
    }

    public function paginateForAdmin(int $page, int $limit, ?int $userId, bool $suspendedOnly): array
    {
        return [];
    }

    public function countForAdmin(?int $userId, bool $suspendedOnly): int
    {
        return 0;
    }
}
