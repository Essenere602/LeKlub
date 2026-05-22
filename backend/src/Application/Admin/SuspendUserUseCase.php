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
use App\DTO\Admin\SuspendUserRequest;
use DateInterval;
use DateTimeImmutable;

final class SuspendUserUseCase
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
    public function execute(int $userId, User $admin, SuspendUserRequest $request): array
    {
        $user = $this->users->findById($userId);

        if ($user === null) {
            throw new ResourceNotFoundException('User not found.');
        }

        if ($user === $admin || ($user->getId() !== null && $user->getId() === $admin->getId())) {
            throw AdminUserActionException::selfSuspensionForbidden();
        }

        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            throw AdminUserActionException::adminSuspensionForbidden();
        }

        $suspendedUntil = (new DateTimeImmutable())->add(new DateInterval('P'.$request->durationDays.'D'));
        $user->suspendUntil($suspendedUntil);
        $this->users->save($user);

        $this->notifications->save(new SystemNotification(
            $user,
            SystemNotificationType::UserSuspended,
            'Compte temporairement suspendu',
            sprintf(
                'Votre compte est suspendu temporairement pendant %d jour%s. Raison : %s',
                $request->durationDays,
                $request->durationDays > 1 ? 's' : '',
                $request->reason
            )
        ));

        return $this->presenter->user($user);
    }
}
