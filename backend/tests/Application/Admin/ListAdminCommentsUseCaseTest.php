<?php

declare(strict_types=1);

namespace App\Tests\Application\Admin;

use App\Application\Admin\AdminPresenter;
use App\Application\Admin\ListAdminCommentsUseCase;
use App\Domain\Entity\Comment;
use App\Domain\Entity\Post;
use App\Domain\Entity\User;
use App\Domain\Entity\UserWarning;
use App\Domain\Repository\AdminCommentModerationRepositoryInterface;
use App\Domain\Repository\PostRepositoryInterface;
use App\Domain\Repository\UserWarningRepositoryInterface;
use App\Domain\ValueObject\AdminContentStatus;
use App\Domain\ValueObject\PostReactionType;
use App\Shared\Api\Pagination;
use PHPUnit\Framework\TestCase;

final class ListAdminCommentsUseCaseTest extends TestCase
{
    public function testItListsDeletedCommentsWithModerationMetadata(): void
    {
        $admin = new User('admin@example.com', 'admin', 'hash');
        $post = new Post(new User('post@example.com', 'post_author', 'hash'), 'Parent post');
        $comment = new Comment($post, new User('author@example.com', 'author', 'hash'), 'Moderated comment');
        $comment->delete($admin);
        $moderationRepository = new ModerationCommentRepository([$comment], 1);

        $useCase = new ListAdminCommentsUseCase(
            $moderationRepository,
            new AdminPresenter(new CommentCountingPostRepository(), new CommentEmptyWarningRepository())
        );

        $result = $useCase->execute(new Pagination(1, 10), AdminContentStatus::Deleted, 'comment');

        self::assertSame(AdminContentStatus::Deleted, $moderationRepository->status);
        self::assertSame('comment', $moderationRepository->query);
        self::assertSame(1, $result['pagination']['total']);
        self::assertSame('Moderated comment', $result['comments'][0]['content']);
        self::assertNotNull($result['comments'][0]['deletedAt']);
        self::assertSame('admin', $result['comments'][0]['deletedBy']['username']);
        self::assertSame('Parent post', $result['comments'][0]['post']['excerpt']);
        self::assertArrayNotHasKey('email', $result['comments'][0]['author']);
    }
}

final class CommentCountingPostRepository implements PostRepositoryInterface
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

final class CommentEmptyWarningRepository implements UserWarningRepositoryInterface
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

final class ModerationCommentRepository implements AdminCommentModerationRepositoryInterface
{
    public ?AdminContentStatus $status = null;
    public ?string $query = null;

    /**
     * @param list<Comment> $comments
     */
    public function __construct(private readonly array $comments, private readonly int $total)
    {
    }

    public function paginateForModeration(AdminContentStatus $status, ?string $query, int $page, int $limit): array
    {
        $this->status = $status;
        $this->query = $query;

        return $this->comments;
    }

    public function countForModeration(AdminContentStatus $status, ?string $query): int
    {
        return $this->total;
    }
}
