<?php

declare(strict_types=1);

namespace App\Application\Admin;

use App\Domain\Repository\UserRepositoryInterface;
use App\Shared\Api\Pagination;

final class ListAdminUsersUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly AdminPresenter $presenter,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(Pagination $pagination, ?string $query): array
    {
        $query = $this->normalizeQuery($query);
        $total = $this->users->countForAdmin($query);
        $users = $this->users->paginateForAdmin($query, $pagination->page, $pagination->limit);

        return [
            'users' => array_map($this->presenter->user(...), $users),
            'pagination' => $pagination->metadata($total),
        ];
    }

    private function normalizeQuery(?string $query): ?string
    {
        $query = trim((string) $query);

        return $query === '' ? null : mb_substr($query, 0, 80);
    }
}
