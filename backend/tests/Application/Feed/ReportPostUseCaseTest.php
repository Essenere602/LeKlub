<?php

declare(strict_types=1);

namespace App\Tests\Application\Feed;

use App\Application\Feed\ReportPostUseCase;
use App\Domain\Entity\FeedReport;
use App\Domain\Entity\Post;
use App\Domain\Entity\User;
use App\Domain\Exception\FeedReportException;
use App\Domain\Exception\ResourceNotFoundException;
use App\Domain\Repository\FeedReportRepositoryInterface;
use App\Domain\Repository\PostRepositoryInterface;
use App\Domain\ValueObject\FeedReportStatus;
use App\Domain\ValueObject\PostReactionType;
use App\DTO\Feed\CreateFeedReportRequest;
use PHPUnit\Framework\TestCase;

final class ReportPostUseCaseTest extends TestCase
{
    public function testItReportsVisiblePost(): void
    {
        $reporter = new User('reporter@example.com', 'reporter', 'hash');
        $post = new Post(new User('author@example.com', 'author', 'hash'), 'Reported content');
        $posts = new ReportingPostRepository($post);
        $reports = new ReportingFeedReportRepository();
        $useCase = new ReportPostUseCase($posts, $reports);

        $report = $useCase->execute(12, $reporter, CreateFeedReportRequest::fromArray([
            'reason' => 'spam',
            'details' => '  Spam repeated  ',
        ]));

        self::assertSame($report, $reports->savedReport);
        self::assertSame($reporter, $report->getReporter());
        self::assertSame($post, $report->getPost());
        self::assertNull($report->getComment());
        self::assertSame('spam', $report->getReason()->value);
        self::assertSame('Spam repeated', $report->getDetails());
        self::assertSame(FeedReportStatus::Open, $report->getStatus());
    }

    public function testItRejectsDuplicatePostReport(): void
    {
        $reporter = new User('reporter@example.com', 'reporter', 'hash');
        $posts = new ReportingPostRepository(new Post(new User('author@example.com', 'author', 'hash'), 'Content'));
        $reports = new ReportingFeedReportRepository();
        $reports->postAlreadyReported = true;
        $useCase = new ReportPostUseCase($posts, $reports);

        $this->expectException(FeedReportException::class);

        $useCase->execute(12, $reporter, CreateFeedReportRequest::fromArray(['reason' => 'spam']));
    }

    public function testItRejectsDeletedOrMissingPost(): void
    {
        $reporter = new User('reporter@example.com', 'reporter', 'hash');
        $useCase = new ReportPostUseCase(
            new ReportingPostRepository(null),
            new ReportingFeedReportRepository()
        );

        $this->expectException(ResourceNotFoundException::class);

        $useCase->execute(12, $reporter, CreateFeedReportRequest::fromArray(['reason' => 'spam']));
    }
}

final class ReportingPostRepository implements PostRepositoryInterface
{
    public function __construct(private readonly ?Post $post)
    {
    }

    public function save(Post $post): void
    {
    }

    public function findVisibleById(int $id): ?Post
    {
        return $this->post;
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

final class ReportingFeedReportRepository implements FeedReportRepositoryInterface
{
    public bool $postAlreadyReported = false;
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
        return $this->postAlreadyReported;
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
