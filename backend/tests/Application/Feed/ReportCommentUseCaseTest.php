<?php

declare(strict_types=1);

namespace App\Tests\Application\Feed;

use App\Application\Feed\ReportCommentUseCase;
use App\Domain\Entity\Comment;
use App\Domain\Entity\FeedReport;
use App\Domain\Entity\Post;
use App\Domain\Entity\User;
use App\Domain\Exception\FeedReportException;
use App\Domain\Exception\ResourceNotFoundException;
use App\Domain\Repository\CommentRepositoryInterface;
use App\Domain\Repository\FeedReportRepositoryInterface;
use App\Domain\ValueObject\FeedReportStatus;
use App\DTO\Feed\CreateFeedReportRequest;
use PHPUnit\Framework\TestCase;

final class ReportCommentUseCaseTest extends TestCase
{
    public function testItReportsVisibleComment(): void
    {
        $reporter = new User('reporter@example.com', 'reporter', 'hash');
        $post = new Post(new User('author@example.com', 'author', 'hash'), 'Post');
        $comment = new Comment($post, new User('commenter@example.com', 'commenter', 'hash'), 'Comment');
        $comments = new ReportingCommentRepository($comment);
        $reports = new CommentReportingFeedReportRepository();
        $useCase = new ReportCommentUseCase($comments, $reports, new \App\Application\User\SuspensionGuard());

        $report = $useCase->execute(18, $reporter, CreateFeedReportRequest::fromArray([
            'reason' => 'insults',
        ]));

        self::assertSame($report, $reports->savedReport);
        self::assertSame($comment, $report->getComment());
        self::assertNull($report->getPost());
        self::assertSame('insults', $report->getReason()->value);
    }

    public function testItRejectsDuplicateCommentReport(): void
    {
        $reporter = new User('reporter@example.com', 'reporter', 'hash');
        $post = new Post(new User('author@example.com', 'author', 'hash'), 'Post');
        $comment = new Comment($post, new User('commenter@example.com', 'commenter', 'hash'), 'Comment');
        $reports = new CommentReportingFeedReportRepository();
        $reports->commentAlreadyReported = true;
        $useCase = new ReportCommentUseCase(new ReportingCommentRepository($comment), $reports, new \App\Application\User\SuspensionGuard());

        $this->expectException(FeedReportException::class);

        $useCase->execute(18, $reporter, CreateFeedReportRequest::fromArray(['reason' => 'spam']));
    }

    public function testItRejectsDeletedOrMissingComment(): void
    {
        $useCase = new ReportCommentUseCase(
            new ReportingCommentRepository(null),
            new CommentReportingFeedReportRepository(),
            new \App\Application\User\SuspensionGuard()
        );

        $this->expectException(ResourceNotFoundException::class);

        $useCase->execute(
            18,
            new User('reporter@example.com', 'reporter', 'hash'),
            CreateFeedReportRequest::fromArray(['reason' => 'spam'])
        );
    }
}

final class ReportingCommentRepository implements CommentRepositoryInterface
{
    public function __construct(private readonly ?Comment $comment)
    {
    }

    public function save(Comment $comment): void
    {
    }

    public function findVisibleById(int $id): ?Comment
    {
        return $this->comment;
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

final class CommentReportingFeedReportRepository implements FeedReportRepositoryInterface
{
    public bool $commentAlreadyReported = false;
    public ?FeedReport $savedReport = null;

    public function save(FeedReport $report): void
    {
        $this->savedReport = $report;
    }

    public function findById(int $id): ?FeedReport
    {
        return null;
    }

    public function existsForReporterAndPost(User $reporter, int $postId): bool
    {
        return false;
    }

    public function existsForReporterAndComment(User $reporter, int $commentId): bool
    {
        return $this->commentAlreadyReported;
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
