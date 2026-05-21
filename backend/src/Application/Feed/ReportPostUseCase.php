<?php

declare(strict_types=1);

namespace App\Application\Feed;

use App\Application\User\SuspensionGuard;
use App\Domain\Entity\FeedReport;
use App\Domain\Entity\User;
use App\Domain\Exception\FeedReportException;
use App\Domain\Exception\ResourceNotFoundException;
use App\Domain\Repository\FeedReportRepositoryInterface;
use App\Domain\Repository\PostRepositoryInterface;
use App\DTO\Feed\CreateFeedReportRequest;

final class ReportPostUseCase
{
    public function __construct(
        private readonly PostRepositoryInterface $posts,
        private readonly FeedReportRepositoryInterface $reports,
        private readonly SuspensionGuard $suspensionGuard,
    ) {
    }

    public function execute(int $postId, User $reporter, CreateFeedReportRequest $request): FeedReport
    {
        $this->suspensionGuard->assertCanWrite($reporter);

        $post = $this->posts->findVisibleById($postId);

        if ($post === null) {
            throw new ResourceNotFoundException('Post not found.');
        }

        if ($this->reports->existsForReporterAndPost($reporter, $postId)) {
            throw FeedReportException::alreadyReported();
        }

        $report = FeedReport::forPost($reporter, $post, $request->reasonEnum(), $request->details);
        $this->reports->save($report);

        return $report;
    }
}
