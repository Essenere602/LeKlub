<?php

declare(strict_types=1);

namespace App\Tests\Application\Admin;

use App\Application\Admin\AdminPresenter;
use App\Application\Admin\ResolveFeedReportUseCase;
use App\Domain\Entity\FeedReport;
use App\Domain\Entity\Post;
use App\Domain\Entity\User;
use App\Domain\Exception\ResourceNotFoundException;
use App\Domain\Repository\FeedReportRepositoryInterface;
use App\Domain\Repository\PostRepositoryInterface;
use App\Domain\ValueObject\FeedReportReason;
use App\Domain\ValueObject\FeedReportStatus;
use App\Domain\ValueObject\PostReactionType;
use PHPUnit\Framework\TestCase;

final class ResolveFeedReportUseCaseTest extends TestCase
{
    public function testItResolvesReport(): void
    {
        $admin = new User('admin@example.com', 'admin', 'hash');
        $report = FeedReport::forPost(
            new User('reporter@example.com', 'reporter', 'hash'),
            new Post(new User('author@example.com', 'author', 'hash'), 'Reported content'),
            FeedReportReason::Spam,
            null
        );
        $reports = new ResolvingFeedReportRepository($report);
        $useCase = new ResolveFeedReportUseCase($reports, new AdminPresenter(new ResolvingPostRepository()));

        $result = $useCase->execute(1, $admin);

        self::assertSame(FeedReportStatus::Resolved, $report->getStatus());
        self::assertSame($admin, $report->getResolvedBy());
        self::assertNotNull($report->getResolvedAt());
        self::assertSame($report, $reports->savedReport);
        self::assertSame('resolved', $result['status']);
    }

    public function testItRejectsUnknownReport(): void
    {
        $useCase = new ResolveFeedReportUseCase(
            new ResolvingFeedReportRepository(null),
            new AdminPresenter(new ResolvingPostRepository())
        );

        $this->expectException(ResourceNotFoundException::class);

        $useCase->execute(404, new User('admin@example.com', 'admin', 'hash'));
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
