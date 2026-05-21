<?php

declare(strict_types=1);

namespace App\Tests\Application\Admin;

use App\Application\Admin\AdminPresenter;
use App\Application\Admin\ListUserWarningsUseCase;
use App\Domain\Entity\FeedReport;
use App\Domain\Entity\Post;
use App\Domain\Entity\User;
use App\Domain\Entity\UserWarning;
use App\Domain\Repository\PostRepositoryInterface;
use App\Domain\Repository\UserWarningRepositoryInterface;
use App\Domain\ValueObject\FeedReportReason;
use App\Domain\ValueObject\PostReactionType;
use App\Shared\Api\Pagination;
use PHPUnit\Framework\TestCase;

final class ListUserWarningsUseCaseTest extends TestCase
{
    public function testItListsWarningsForAdmin(): void
    {
        $user = new User('warned@example.com', 'warned', 'hash');
        $admin = new User('admin@example.com', 'admin', 'hash');
        $report = FeedReport::forPost(new User('reporter@example.com', 'reporter', 'hash'), new Post($user, 'Content'), FeedReportReason::Spam, null);
        $warning = new UserWarning($user, $report, $admin, 'post', 12, 'Moderation note');
        $warnings = new AdminWarningRepository([$warning], 1);
        $useCase = new ListUserWarningsUseCase($warnings, new AdminPresenter(new WarningPostRepository(), $warnings));

        $result = $useCase->execute(new Pagination(2, 10), 7, true);

        self::assertSame(1, $result['pagination']['total']);
        self::assertSame('warned', $result['warnings'][0]['user']['username']);
        self::assertSame('Moderation note', $result['warnings'][0]['reason']);
        self::assertSame(1, $result['warnings'][0]['warningCount']);
        self::assertSame(2, $warnings->page);
        self::assertSame(10, $warnings->limit);
        self::assertSame(7, $warnings->userId);
        self::assertTrue($warnings->suspendedOnly);
    }
}

final class AdminWarningRepository implements UserWarningRepositoryInterface
{
    public ?int $page = null;
    public ?int $limit = null;
    public ?int $userId = null;
    public bool $suspendedOnly = false;

    /**
     * @param list<UserWarning> $warnings
     */
    public function __construct(private readonly array $warnings, private readonly int $total)
    {
    }

    public function save(UserWarning $warning): void
    {
    }

    public function countForUser(User $user): int
    {
        return 1;
    }

    public function paginateForAdmin(int $page, int $limit, ?int $userId, bool $suspendedOnly): array
    {
        $this->page = $page;
        $this->limit = $limit;
        $this->userId = $userId;
        $this->suspendedOnly = $suspendedOnly;

        return $this->warnings;
    }

    public function countForAdmin(?int $userId, bool $suspendedOnly): int
    {
        return $this->total;
    }
}

final class WarningPostRepository implements PostRepositoryInterface
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
