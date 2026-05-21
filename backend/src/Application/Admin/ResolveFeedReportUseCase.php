<?php

declare(strict_types=1);

namespace App\Application\Admin;

use App\Application\Feed\DeleteCommentUseCase;
use App\Application\Feed\DeletePostUseCase;
use App\Domain\Entity\Comment;
use App\Domain\Entity\FeedReport;
use App\Domain\Entity\Post;
use App\Domain\Entity\SystemNotification;
use App\Domain\Entity\User;
use App\Domain\Entity\UserWarning;
use App\Domain\Exception\FeedReportException;
use App\Domain\Exception\ResourceNotFoundException;
use App\Domain\Repository\FeedReportRepositoryInterface;
use App\Domain\Repository\SystemNotificationRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\Repository\UserWarningRepositoryInterface;
use App\Domain\ValueObject\FeedReportDecision;
use App\Domain\ValueObject\FeedReportStatus;
use App\Domain\ValueObject\SystemNotificationType;
use App\DTO\Admin\ResolveFeedReportRequest;
use DateInterval;
use DateTimeImmutable;

final class ResolveFeedReportUseCase
{
    private const WARNING_SUSPENSION_THRESHOLD = 3;
    private const SUSPENSION_DAYS = 7;

    public function __construct(
        private readonly FeedReportRepositoryInterface $reports,
        private readonly UserWarningRepositoryInterface $warnings,
        private readonly UserRepositoryInterface $users,
        private readonly SystemNotificationRepositoryInterface $notifications,
        private readonly DeletePostUseCase $deletePost,
        private readonly DeleteCommentUseCase $deleteComment,
        private readonly AdminPresenter $presenter,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(int $reportId, User $admin, ResolveFeedReportRequest $request): array
    {
        $report = $this->reports->findById($reportId);

        if ($report === null) {
            throw new ResourceNotFoundException('Report not found.');
        }

        if ($report->getStatus() === FeedReportStatus::Resolved) {
            throw FeedReportException::alreadyResolved();
        }

        $decision = $request->decisionEnum();

        if ($decision === FeedReportDecision::ContentRemoved) {
            $this->removeReportedContentAndWarnAuthor($report, $admin, $request->adminNote);
        } else {
            $this->notifications->save(new SystemNotification(
                $report->getReporter(),
                SystemNotificationType::ReportRejected,
                'Signalement traité',
                "Votre signalement a été étudié. Il n'a pas été retenu par la modération."
            ));
        }

        $report->resolve($admin, $decision, $request->adminNote);
        $this->reports->save($report);

        return $this->presenter->report($report);
    }

    private function removeReportedContentAndWarnAuthor(FeedReport $report, User $admin, ?string $reason): void
    {
        $post = $report->getPost();
        $comment = $report->getComment();
        $content = $post ?? $comment;

        if (!$content instanceof Post && !$content instanceof Comment) {
            throw FeedReportException::contentUnavailable();
        }

        if ($content->isDeleted()) {
            throw FeedReportException::contentUnavailable();
        }

        $author = $content->getAuthor();

        if ($post instanceof Post) {
            $this->deletePost->execute($post, $admin);
            $contentType = 'post';
        } else {
            $this->deleteComment->execute($comment, $admin);
            $contentType = 'comment';
        }

        $warning = new UserWarning(
            $author,
            $report,
            $admin,
            $contentType,
            (int) $content->getId(),
            $reason
        );
        $this->warnings->save($warning);

        $this->notifications->save(new SystemNotification(
            $report->getReporter(),
            SystemNotificationType::ReportAccepted,
            'Signalement confirmé',
            'Merci pour votre signalement. Le contenu concerné a été supprimé par la modération.'
        ));

        $this->notifications->save(new SystemNotification(
            $author,
            SystemNotificationType::Warning,
            'Avertissement',
            'Un de vos contenus a été supprimé suite à un signalement validé par la modération.'
        ));

        if ($this->warnings->countForUser($author) >= self::WARNING_SUSPENSION_THRESHOLD) {
            $author->suspendUntil((new DateTimeImmutable())->add(new DateInterval('P'.self::SUSPENSION_DAYS.'D')));
            $this->users->save($author);

            $this->notifications->save(new SystemNotification(
                $author,
                SystemNotificationType::Suspension,
                'Compte temporairement suspendu',
                'Votre compte est temporairement suspendu pour 7 jours après plusieurs avertissements.'
            ));
        }
    }
}
