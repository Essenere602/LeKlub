<?php

declare(strict_types=1);

namespace App\Application\Admin;

use App\Domain\Repository\FeedReportRepositoryInterface;
use App\Domain\ValueObject\FeedReportStatus;
use App\Shared\Api\Pagination;
use InvalidArgumentException;

final class ListFeedReportsUseCase
{
    public function __construct(
        private readonly FeedReportRepositoryInterface $reports,
        private readonly AdminPresenter $presenter,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(Pagination $pagination, ?string $status): array
    {
        $statusFilter = $this->statusFromString($status);

        return [
            'reports' => array_map(
                $this->presenter->report(...),
                $this->reports->paginateForAdmin($pagination->page, $pagination->limit, $statusFilter)
            ),
            'pagination' => $pagination->metadata($this->reports->countForAdmin($statusFilter)),
        ];
    }

    private function statusFromString(?string $status): ?FeedReportStatus
    {
        if ($status === null || trim($status) === '') {
            return null;
        }

        return FeedReportStatus::tryFrom($status) ?? throw new InvalidArgumentException('Invalid report status.');
    }
}
