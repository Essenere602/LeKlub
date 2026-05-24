<?php

declare(strict_types=1);

namespace App\Application\Admin;

use App\Domain\Entity\FeedReport;
use App\Domain\Entity\SystemNotification;
use App\Domain\Entity\User;
use App\Domain\Entity\UserWarning;
use App\Domain\Repository\SystemNotificationRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\Repository\UserWarningRepositoryInterface;
use App\Domain\ValueObject\SystemNotificationType;
use DateInterval;
use DateTimeImmutable;

final class ApplyUserWarningPolicy
{
    private const WARNING_SUSPENSION_THRESHOLD = 3;
    private const SUSPENSION_DAYS = 7;

    public function __construct(
        private readonly UserWarningRepositoryInterface $warnings,
        private readonly UserRepositoryInterface $users,
        private readonly SystemNotificationRepositoryInterface $notifications,
    ) {
    }

    public function apply(
        User $author,
        FeedReport $report,
        User $admin,
        string $contentType,
        int $contentId,
        ?string $reason,
    ): void {
        $this->warnings->save(new UserWarning(
            $author,
            $report,
            $admin,
            $contentType,
            $contentId,
            $reason
        ));

        $this->notifications->save(new SystemNotification(
            $author,
            SystemNotificationType::Warning,
            'Avertissement',
            'Un de vos contenus a été supprimé suite à un signalement validé par la modération.'
        ));

        if ($this->warnings->countForUser($author) < self::WARNING_SUSPENSION_THRESHOLD) {
            return;
        }

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
