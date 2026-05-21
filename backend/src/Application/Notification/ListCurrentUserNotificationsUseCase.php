<?php

declare(strict_types=1);

namespace App\Application\Notification;

use App\Domain\Entity\User;
use App\Domain\Repository\SystemNotificationRepositoryInterface;
use App\Shared\Api\Pagination;

final class ListCurrentUserNotificationsUseCase
{
    public function __construct(
        private readonly SystemNotificationRepositoryInterface $notifications,
        private readonly NotificationPresenter $presenter,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(User $user, Pagination $pagination): array
    {
        return [
            'notifications' => array_map(
                $this->presenter->notification(...),
                $this->notifications->paginateForUser($user, $pagination->page, $pagination->limit)
            ),
            'pagination' => $pagination->metadata($this->notifications->countForUser($user)),
        ];
    }
}
