<?php

declare(strict_types=1);

namespace App\Application\Admin;

use App\Domain\Repository\AdminCommentModerationRepositoryInterface;
use App\Domain\ValueObject\AdminContentStatus;
use App\Shared\Api\Pagination;

final class ListAdminCommentsUseCase
{
    public function __construct(
        private readonly AdminCommentModerationRepositoryInterface $comments,
        private readonly AdminPresenter $presenter,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(Pagination $pagination, AdminContentStatus $status, ?string $query): array
    {
        return [
            'comments' => array_map(
                $this->presenter->comment(...),
                $this->comments->paginateForModeration($status, $query, $pagination->page, $pagination->limit)
            ),
            'pagination' => $pagination->metadata($this->comments->countForModeration($status, $query)),
        ];
    }
}
