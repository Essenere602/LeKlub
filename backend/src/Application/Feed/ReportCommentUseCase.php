<?php

declare(strict_types=1);

namespace App\Application\Feed;

use App\Application\User\SuspensionGuard;
use App\Domain\Entity\FeedReport;
use App\Domain\Entity\User;
use App\Domain\Exception\FeedReportException;
use App\Domain\Exception\ResourceNotFoundException;
use App\Domain\Repository\CommentRepositoryInterface;
use App\Domain\Repository\FeedReportRepositoryInterface;
use App\DTO\Feed\CreateFeedReportRequest;

final class ReportCommentUseCase
{
    public function __construct(
        private readonly CommentRepositoryInterface $comments,
        private readonly FeedReportRepositoryInterface $reports,
        private readonly SuspensionGuard $suspensionGuard,
    ) {
    }

    public function execute(int $commentId, User $reporter, CreateFeedReportRequest $request): FeedReport
    {
        $this->suspensionGuard->assertCanWrite($reporter);

        $comment = $this->comments->findVisibleById($commentId);

        if ($comment === null) {
            throw new ResourceNotFoundException('Comment not found.');
        }

        if ($this->reports->existsForReporterAndComment($reporter, $commentId)) {
            throw FeedReportException::alreadyReported();
        }

        $report = FeedReport::forComment($reporter, $comment, $request->reasonEnum(), $request->details);
        $this->reports->save($report);

        return $report;
    }
}
