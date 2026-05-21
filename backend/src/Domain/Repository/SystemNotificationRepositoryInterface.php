<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\SystemNotification;
use App\Domain\Entity\User;

interface SystemNotificationRepositoryInterface
{
    public function save(SystemNotification $notification): void;

    public function findForUser(int $id, User $user): ?SystemNotification;

    /**
     * @return list<SystemNotification>
     */
    public function paginateForUser(User $user, int $page, int $limit): array;

    public function countForUser(User $user): int;
}
