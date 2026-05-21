<?php

declare(strict_types=1);

namespace App\Application\Notification;

use App\Domain\Entity\SystemNotification;

final class NotificationPresenter
{
    /**
     * @return array<string, mixed>
     */
    public function notification(SystemNotification $notification): array
    {
        return [
            'id' => $notification->getId(),
            'type' => $notification->getType()->value,
            'title' => $notification->getTitle(),
            'message' => $notification->getMessage(),
            'createdAt' => $notification->getCreatedAt()->format(DATE_ATOM),
            'readAt' => $notification->getReadAt()?->format(DATE_ATOM),
        ];
    }
}
