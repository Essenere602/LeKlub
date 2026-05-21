<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\User;
use App\Domain\Entity\UserWarning;

interface UserWarningRepositoryInterface
{
    public function save(UserWarning $warning): void;

    public function countForUser(User $user): int;
}
