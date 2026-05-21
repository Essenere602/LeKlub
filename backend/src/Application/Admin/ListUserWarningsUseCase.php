<?php

declare(strict_types=1);

namespace App\Application\Admin;

use App\Domain\Repository\UserWarningRepositoryInterface;
use App\Shared\Api\Pagination;

final class ListUserWarningsUseCase
{
    public function __construct(
        private readonly UserWarningRepositoryInterface $warnings,
        private readonly AdminPresenter $presenter,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(Pagination $pagination, ?int $userId, bool $suspendedOnly): array
    {
        return [
            'warnings' => array_map(
                $this->presenter->warning(...),
                $this->warnings->paginateForAdmin($pagination->page, $pagination->limit, $userId, $suspendedOnly)
            ),
            'pagination' => $pagination->metadata($this->warnings->countForAdmin($userId, $suspendedOnly)),
        ];
    }
}
