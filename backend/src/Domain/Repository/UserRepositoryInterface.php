<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\User;

interface UserRepositoryInterface
{
    public function findOneByEmail(string $email): ?User;

    public function findOneByUsername(string $username): ?User;

    public function findById(int $id): ?User;

    /**
     * @return list<User>
     */
    public function findForDirectory(User $currentUser, ?string $query, int $limit): array;

    /**
     * @return list<User>
     */
    public function paginateForAdmin(?string $query, int $page, int $limit): array;

    public function countForAdmin(?string $query): int;

    public function save(User $user): void;
}
