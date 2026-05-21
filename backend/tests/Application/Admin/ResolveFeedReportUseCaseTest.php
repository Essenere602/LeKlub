<?php

declare(strict_types=1);

namespace App\Tests\Application\Admin;

use App\Application\Admin\AdminPresenter;
use App\Application\Admin\ResolveFeedReportUseCase;
use App\Application\Feed\DeleteCommentUseCase;
use App\Application\Feed\DeletePostUseCase;
use App\Domain\Entity\Comment;
use App\Domain\Entity\FeedReport;
use App\Domain\Entity\Post;
use App\Domain\Entity\User;
use App\Domain\Entity\UserWarning;
use App\Domain\Exception\FeedReportException;
use App\Domain\Exception\ResourceNotFoundException;
use App\Domain\Repository\CommentRepositoryInterface;
use App\Domain\Repository\FeedReportRepositoryInterface;
use App\Domain\Repository\PostRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\Repository\UserWarningRepositoryInterface;
use App\Domain\ValueObject\FeedReportDecision;
use App\Domain\ValueObject\FeedReportReason;
use App\Domain\ValueObject\FeedReportStatus;
use App\Domain\ValueObject\PostReactionType;
use App\DTO\Admin\ResolveFeedReportRequest;
use PHPUnit\Framework\TestCase;

final class ResolveFeedReportUseCaseTest extends TestCase
{
    public function testItRejectsReportWithoutDeletingContent(): void
    {
        $admin = new User('admin@example.com', 'admin', 'hash');
        $post = new Post(new User('author@example.com', 'author', 'hash'), 'Reported content');
        $report = FeedReport::forPost(new User('reporter@example.com', 'reporter', 'hash'), $post, FeedReportReason::Spam, null);
        $reports = new ResolvingFeedReportRepository($report);
        $useCase = self::useCase($reports);

        $result = $useCase->execute(1, $admin, ResolveFeedReportRequest::fromArray([
            'decision' => 'rejected',
            'adminNote' => 'Not justified',
        ]));

        self::assertFalse($post->isDeleted());
        self::assertSame(FeedReportStatus::Resolved, $report->getStatus());
        self::assertSame(FeedReportDecision::Rejected, $report->getDecision());
        self::assertSame('Not justified', $report->getAdminNote());
        self::assertSame($admin, $report->getResolvedBy());
        self::assertSame($report, $reports->savedReport);
        self::assertSame('rejected', $result['decision']);
    }

    public function testItRemovesReportedPostAndWarnsAuthor(): void
    {
        $admin = new User('admin@example.com', 'admin', 'hash');
        $author = new User('author@example.com', 'author', 'hash');
        $post = new Post($author, 'Reported content');
        $report = FeedReport::forPost(new User('reporter@example.com', 'reporter', 'hash'), $post, FeedReportReason::Spam, null);
        $reports = new ResolvingFeedReportRepository($report);
        $warnings = new ResolvingWarningRepository();
        $useCase = self::useCase($reports, $warnings);

        $useCase->execute(1, $admin, ResolveFeedReportRequest::fromArray([
            'decision' => 'content_removed',
            'adminNote' => 'Moderation reason',
        ]));

        self::assertTrue($post->isDeleted());
        self::assertSame($admin, $post->getDeletedBy());
        self::assertSame(FeedReportDecision::ContentRemoved, $report->getDecision());
        self::assertInstanceOf(UserWarning::class, $warnings->savedWarning);
        self::assertSame($author, $warnings->savedWarning->getUser());
    }

    public function testItSuspendsAuthorAfterThreeWarnings(): void
    {
        $admin = new User('admin@example.com', 'admin', 'hash');
        $author = new User('author@example.com', 'author', 'hash');
        $post = new Post($author, 'Reported content');
        $report = FeedReport::forPost(new User('reporter@example.com', 'reporter', 'hash'), $post, FeedReportReason::Spam, null);
        $warnings = new ResolvingWarningRepository();
        $warnings->warningCount = 3;
        $users = new ResolvingUserRepository();
        $useCase = self::useCase(new ResolvingFeedReportRepository($report), $warnings, $users);

        $useCase->execute(1, $admin, ResolveFeedReportRequest::fromArray([
            'decision' => 'content_removed',
        ]));

        self::assertTrue($author->isSuspended());
        self::assertSame($author, $users->savedUser);
    }

    public function testItRejectsAlreadyResolvedReport(): void
    {
        $admin = new User('admin@example.com', 'admin', 'hash');
        $report = FeedReport::forPost(
            new User('reporter@example.com', 'reporter', 'hash'),
            new Post(new User('author@example.com', 'author', 'hash'), 'Reported content'),
            FeedReportReason::Spam,
            null
        );
        $report->resolve($admin, FeedReportDecision::Rejected, null);
        $useCase = self::useCase(new ResolvingFeedReportRepository($report));

        $this->expectException(FeedReportException::class);

        $useCase->execute(1, $admin, ResolveFeedReportRequest::fromArray(['decision' => 'content_removed']));
    }

    public function testItRejectsUnknownReport(): void
    {
        $useCase = self::useCase(new ResolvingFeedReportRepository(null));

        $this->expectException(ResourceNotFoundException::class);

        $useCase->execute(404, new User('admin@example.com', 'admin', 'hash'), ResolveFeedReportRequest::fromArray([
            'decision' => 'rejected',
        ]));
    }

    private static function useCase(
        ?ResolvingFeedReportRepository $reports = null,
        ?ResolvingWarningRepository $warnings = null,
        ?ResolvingUserRepository $users = null,
    ): ResolveFeedReportUseCase {
        $postRepository = new ResolvingPostRepository();
        $commentRepository = new ResolvingCommentRepository();

        return new ResolveFeedReportUseCase(
            $reports ?? new ResolvingFeedReportRepository(null),
            $warnings ?? new ResolvingWarningRepository(),
            $users ?? new ResolvingUserRepository(),
            new DeletePostUseCase($postRepository),
            new DeleteCommentUseCase($commentRepository),
            new AdminPresenter($postRepository),
        );
    }
}

final class ResolvingFeedReportRepository implements FeedReportRepositoryInterface
{
    public ?FeedReport $savedReport = null;

    public function __construct(private readonly ?FeedReport $report)
    {
    }

    public function save(FeedReport $report): void
    {
        $this->savedReport = $report;
    }

    public function findById(int $id): ?FeedReport
    {
        return $this->report;
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
        return 0;
    }
}

final class ResolvingWarningRepository implements UserWarningRepositoryInterface
{
    public ?UserWarning $savedWarning = null;
    public int $warningCount = 1;

    public function save(UserWarning $warning): void
    {
        $this->savedWarning = $warning;
    }

    public function countForUser(User $user): int
    {
        return $this->warningCount;
    }
}

final class ResolvingUserRepository implements UserRepositoryInterface
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

final class ResolvingPostRepository implements PostRepositoryInterface
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

final class ResolvingCommentRepository implements CommentRepositoryInterface
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
        return 0;
    }
}
