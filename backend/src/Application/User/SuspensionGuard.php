<?php

declare(strict_types=1);

namespace App\Application\User;

use App\Domain\Entity\User;
use App\Domain\Exception\UserSuspendedException;

final class SuspensionGuard
{
    public function assertCanWrite(User $user): void
    {
        if ($user->isSuspended()) {
            throw UserSuspendedException::forWriteAction();
        }
    }
}
