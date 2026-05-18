<?php

declare(strict_types=1);

namespace App\Application\Admin;

use App\Domain\Repository\CommentRepositoryInterface;
use App\Shared\Api\Pagination;

final class ListAdminCommentsUseCase
{
    public function __construct(
        private readonly CommentRepositoryInterface $comments,
        private readonly AdminPresenter $presenter,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(Pagination $pagination): array
    {
        return [
            'comments' => array_map(
                $this->presenter->comment(...),
                $this->comments->paginateVisibleForAdmin($pagination->page, $pagination->limit)
            ),
            'pagination' => $pagination->metadata($this->comments->countVisible()),
        ];
    }
}
