<?php

declare(strict_types=1);

namespace App\Application\Admin;

use App\Domain\Repository\AdminPostModerationRepositoryInterface;
use App\Domain\ValueObject\AdminContentStatus;
use App\Shared\Api\Pagination;

final class ListAdminPostsUseCase
{
    public function __construct(
        private readonly AdminPostModerationRepositoryInterface $posts,
        private readonly AdminPresenter $presenter,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(Pagination $pagination, AdminContentStatus $status, ?string $query): array
    {
        return [
            'posts' => array_map(
                $this->presenter->post(...),
                $this->posts->paginateForModeration($status, $query, $pagination->page, $pagination->limit)
            ),
            'pagination' => $pagination->metadata($this->posts->countForModeration($status, $query)),
        ];
    }
}
