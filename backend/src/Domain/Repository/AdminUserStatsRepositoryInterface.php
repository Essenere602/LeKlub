<?php

declare(strict_types=1);

namespace App\Domain\Repository;

interface AdminUserStatsRepositoryInterface
{
    public function countSuspendedUsers(): int;
}
