<?php

declare(strict_types=1);

namespace App\Application\Admin;

use App\Domain\Entity\SystemNotification;
use App\Domain\Entity\User;
use App\Domain\Exception\AdminUserActionException;
use App\Domain\Exception\ResourceNotFoundException;
use App\Domain\Repository\AdminRoleUserRepositoryInterface;
use App\Domain\Repository\SystemNotificationRepositoryInterface;
use App\Domain\ValueObject\SystemNotificationType;

final class DemoteAdminUserUseCase
{
    public function __construct(
        private readonly AdminRoleUserRepositoryInterface $users,
        private readonly SystemNotificationRepositoryInterface $notifications,
        private readonly AdminPresenter $presenter,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(int $userId, User $admin): array
    {
        $user = $this->users->findById($userId);

        if ($user === null) {
            throw new ResourceNotFoundException('User not found.');
        }

        if ($user === $admin || ($user->getId() !== null && $user->getId() === $admin->getId())) {
            throw AdminUserActionException::selfRoleChangeForbidden();
        }

        if (!$user->isAdmin()) {
            return $this->presenter->user($user);
        }

        if ($user->isAdmin() && $this->users->countAdmins() <= 1) {
            throw AdminUserActionException::lastAdminDemotionForbidden();
        }

        $user->demoteFromAdmin();
        $this->users->save($user);

        $this->notifications->save(new SystemNotification(
            $user,
            SystemNotificationType::AdminRoleRemoved,
            'Rôle administrateur retiré',
            'Votre compte ne dispose plus du rôle administrateur sur LeKlub.'
        ));

        return $this->presenter->user($user);
    }
}
