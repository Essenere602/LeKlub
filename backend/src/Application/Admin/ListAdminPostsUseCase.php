<?php

declare(strict_types=1);

namespace App\Application\Admin;

use App\Domain\Repository\PostRepositoryInterface;
use App\Shared\Api\Pagination;

final class ListAdminPostsUseCase
{
    public function __construct(
        private readonly PostRepositoryInterface $posts,
        private readonly AdminPresenter $presenter,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(Pagination $pagination): array
    {
        return [
            'posts' => array_map(
                $this->presenter->post(...),
                $this->posts->paginateVisibleForAdmin($pagination->page, $pagination->limit)
            ),
            'pagination' => $pagination->metadata($this->posts->countVisible()),
        ];
    }
}
