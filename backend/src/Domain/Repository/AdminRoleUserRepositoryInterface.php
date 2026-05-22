<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\User;

interface AdminRoleUserRepositoryInterface
{
    public function findById(int $id): ?User;

    public function countAdmins(): int;

    public function save(User $user): void;
}
