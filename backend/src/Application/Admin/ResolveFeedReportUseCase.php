<?php

declare(strict_types=1);

namespace App\Application\Admin;

use App\Domain\Entity\User;
use App\Domain\Exception\ResourceNotFoundException;
use App\Domain\Repository\FeedReportRepositoryInterface;

final class ResolveFeedReportUseCase
{
    public function __construct(
        private readonly FeedReportRepositoryInterface $reports,
        private readonly AdminPresenter $presenter,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(int $reportId, User $admin): array
    {
        $report = $this->reports->findById($reportId);

        if ($report === null) {
            throw new ResourceNotFoundException('Report not found.');
        }

        $report->resolve($admin);
        $this->reports->save($report);

        return $this->presenter->report($report);
    }
}
