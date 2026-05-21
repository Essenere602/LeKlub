<?php

declare(strict_types=1);

namespace App\Application\Notification;

use App\Domain\Entity\User;
use App\Domain\Exception\ResourceNotFoundException;
use App\Domain\Repository\SystemNotificationRepositoryInterface;

final class MarkNotificationAsReadUseCase
{
    public function __construct(
        private readonly SystemNotificationRepositoryInterface $notifications,
        private readonly NotificationPresenter $presenter,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(int $notificationId, User $user): array
    {
        $notification = $this->notifications->findForUser($notificationId, $user);

        if ($notification === null) {
            throw new ResourceNotFoundException('Notification not found.');
        }

        $notification->markAsRead();
        $this->notifications->save($notification);

        return $this->presenter->notification($notification);
    }
}
