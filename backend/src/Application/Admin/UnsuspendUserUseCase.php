<?php

declare(strict_types=1);

namespace App\Application\Admin;

use App\Domain\Entity\SystemNotification;
use App\Domain\Entity\User;
use App\Domain\Exception\AdminUserActionException;
use App\Domain\Exception\ResourceNotFoundException;
use App\Domain\Repository\SystemNotificationRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\SystemNotificationType;
use App\DTO\Admin\UnsuspendUserRequest;

final class UnsuspendUserUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly SystemNotificationRepositoryInterface $notifications,
        private readonly AdminPresenter $presenter,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(int $userId, User $admin, UnsuspendUserRequest $request): array
    {
        $user = $this->users->findById($userId);

        if ($user === null) {
            throw new ResourceNotFoundException('User not found.');
        }

        if ($user === $admin || ($user->getId() !== null && $user->getId() === $admin->getId())) {
            throw AdminUserActionException::selfUnsuspensionForbidden();
        }

        $user->unsuspend();
        $this->users->save($user);

        $message = 'Votre suspension temporaire a été levée par la modération.';
        if ($request->reason !== null) {
            $message .= ' Raison : '.$request->reason;
        }

        $this->notifications->save(new SystemNotification(
            $user,
            SystemNotificationType::UserUnsuspended,
            'Suspension levée',
            $message
        ));

        return $this->presenter->user($user);
    }
}
