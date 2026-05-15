<?php

declare(strict_types=1);

namespace App\Application\Feed;

use App\Domain\Repository\PostRepositoryInterface;
use App\Shared\Api\Pagination;

final class ListFeedUseCase
{
    public function __construct(
        private readonly PostRepositoryInterface $posts,
        private readonly FeedPresenter $presenter,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(Pagination $pagination): array
    {
        $items = array_map(
            fn ($post): array => $this->presenter->post($post),
            $this->posts->paginateVisible($pagination->page, $pagination->limit)
        );

        return [
            'posts' => $items,
            'pagination' => $pagination->metadata($this->posts->countVisible()),
        ];
    }
}
