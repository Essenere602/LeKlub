<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\FeedReport;
use App\Domain\Entity\User;
use App\Domain\ValueObject\FeedReportStatus;

interface FeedReportRepositoryInterface
{
    public function save(FeedReport $report): void;

    public function findById(int $id): ?FeedReport;

    public function existsForReporterAndPost(User $reporter, int $postId): bool;

    public function existsForReporterAndComment(User $reporter, int $commentId): bool;

    /**
     * @return list<FeedReport>
     */
    public function paginateForAdmin(int $page, int $limit, ?FeedReportStatus $status): array;

    public function countForAdmin(?FeedReportStatus $status): int;
}
