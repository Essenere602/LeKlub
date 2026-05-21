<?php

declare(strict_types=1);

namespace App\Tests\Application\Admin;

use App\Application\Admin\GetAdminOverviewUseCase;
use App\Domain\Entity\Comment;
use App\Domain\Entity\Conversation;
use App\Domain\Entity\Message;
use App\Domain\Entity\Post;
use App\Domain\Entity\User;
use App\Domain\Entity\UserWarning;
use App\Domain\Repository\AdminCommentModerationRepositoryInterface;
use App\Domain\Repository\AdminPostModerationRepositoryInterface;
use App\Domain\Repository\AdminUserStatsRepositoryInterface;
use App\Domain\Repository\CommentRepositoryInterface;
use App\Domain\Repository\ConversationRepositoryInterface;
use App\Domain\Repository\FeedReportRepositoryInterface;
use App\Domain\Repository\MessageRepositoryInterface;
use App\Domain\Repository\PostRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\Repository\UserWarningRepositoryInterface;
use App\Domain\ValueObject\AdminContentStatus;
use App\Domain\ValueObject\FeedReportStatus;
use App\Domain\ValueObject\PostReactionType;
use PHPUnit\Framework\TestCase;

final class GetAdminOverviewUseCaseTest extends TestCase
{
    public function testItReturnsUsefulModerationStats(): void
    {
        $useCase = new GetAdminOverviewUseCase(
            new OverviewUserRepository(),
            new OverviewPostRepository(),
            new OverviewCommentRepository(),
            new OverviewConversationRepository(),
            new OverviewMessageRepository(),
            new OverviewReportRepository(),
            new OverviewWarningRepository(),
            new OverviewAdminPostRepository(),
            new OverviewAdminCommentRepository(),
            new OverviewUserStatsRepository(),
        );

        $result = $useCase->execute();

        self::assertSame(12, $result['usersCount']);
        self::assertSame(20, $result['postsCount']);
        self::assertSame(31, $result['commentsCount']);
        self::assertSame(4, $result['openReportsCount']);
        self::assertSame(2, $result['deletedPostsCount']);
        self::assertSame(3, $result['deletedCommentsCount']);
        self::assertSame(1, $result['suspendedUsersCount']);
        self::assertSame(7, $result['warningsCount']);
    }
}

final class OverviewUserRepository implements UserRepositoryInterface
{
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
        return 12;
    }

    public function save(User $user): void
    {
    }
}

final class OverviewUserStatsRepository implements AdminUserStatsRepositoryInterface
{
    public function countSuspendedUsers(): int
    {
        return 1;
    }
}

final class OverviewPostRepository implements PostRepositoryInterface
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
        return 20;
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

final class OverviewAdminPostRepository implements AdminPostModerationRepositoryInterface
{
    public function paginateForModeration(AdminContentStatus $status, ?string $query, int $page, int $limit): array
    {
        return [];
    }

    public function countForModeration(AdminContentStatus $status, ?string $query): int
    {
        return $status === AdminContentStatus::Deleted ? 2 : 0;
    }
}

final class OverviewCommentRepository implements CommentRepositoryInterface
{
    public function save(Comment $comment): void
    {
    }

    public function findVisibleById(int $id): ?Comment
    {
        return null;
    }

    public function paginateVisibleForPost(Post $post, int $page, int $limit): array
    {
        return [];
    }

    public function countVisibleForPost(Post $post): int
    {
        return 0;
    }

    public function paginateVisibleForAdmin(int $page, int $limit): array
    {
        return [];
    }

    public function countVisible(): int
    {
        return 31;
    }
}

final class OverviewAdminCommentRepository implements AdminCommentModerationRepositoryInterface
{
    public function paginateForModeration(AdminContentStatus $status, ?string $query, int $page, int $limit): array
    {
        return [];
    }

    public function countForModeration(AdminContentStatus $status, ?string $query): int
    {
        return $status === AdminContentStatus::Deleted ? 3 : 0;
    }
}

final class OverviewConversationRepository implements ConversationRepositoryInterface
{
    public function save(Conversation $conversation): void
    {
    }

    public function findById(int $id): ?Conversation
    {
        return null;
    }

    public function findBetweenUsers(User $first, User $second): ?Conversation
    {
        return null;
    }

    public function findForUser(User $user): array
    {
        return [];
    }

    public function countAll(): int
    {
        return 5;
    }
}

final class OverviewMessageRepository implements MessageRepositoryInterface
{
    public function save(Message $message): void
    {
    }

    public function findForConversation(Conversation $conversation): array
    {
        return [];
    }

    public function findVisibleForConversationAndUser(Conversation $conversation, User $user): array
    {
        return [];
    }

    public function markAsReadForRecipient(Conversation $conversation, User $recipient): void
    {
    }

    public function countUnreadForRecipient(Conversation $conversation, User $recipient): int
    {
        return 0;
    }

    public function countVisibleUnreadForRecipient(Conversation $conversation, User $recipient): int
    {
        return 0;
    }

    public function findLastForConversation(Conversation $conversation): ?Message
    {
        return null;
    }

    public function findLastVisibleForConversationAndUser(Conversation $conversation, User $user): ?Message
    {
        return null;
    }

    public function findById(int $id): ?Message
    {
        return null;
    }

    public function countAll(): int
    {
        return 8;
    }
}

final class OverviewReportRepository implements FeedReportRepositoryInterface
{
    public function save(\App\Domain\Entity\FeedReport $report): void
    {
    }

    public function findById(int $id): ?\App\Domain\Entity\FeedReport
    {
        return null;
    }

    public function existsForReporterAndPost(User $reporter, int $postId): bool
    {
        return false;
    }

    public function existsForReporterAndComment(User $reporter, int $commentId): bool
    {
        return false;
    }

    public function paginateForAdmin(int $page, int $limit, ?FeedReportStatus $status): array
    {
        return [];
    }

    public function countForAdmin(?FeedReportStatus $status): int
    {
        return 4;
    }
}

final class OverviewWarningRepository implements UserWarningRepositoryInterface
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
        return 7;
    }
}
