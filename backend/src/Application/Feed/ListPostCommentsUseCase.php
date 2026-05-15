<?php

declare(strict_types=1);

namespace App\Application\Feed;

use App\Domain\Exception\ResourceNotFoundException;
use App\Domain\Repository\CommentRepositoryInterface;
use App\Domain\Repository\PostRepositoryInterface;
use App\Shared\Api\Pagination;

final class ListPostCommentsUseCase
{
    public function __construct(
        private readonly PostRepositoryInterface $posts,
        private readonly CommentRepositoryInterface $comments,
        private readonly FeedPresenter $presenter,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(int $postId, Pagination $pagination): array
    {
        $post = $this->posts->findVisibleById($postId);

        if ($post === null) {
            throw ResourceNotFoundException::post();
        }

        $comments = array_map(
            fn ($comment): array => $this->presenter->comment($comment),
            $this->comments->paginateVisibleForPost($post, $pagination->page, $pagination->limit)
        );

        return [
            'comments' => $comments,
            'pagination' => $pagination->metadata($this->comments->countVisibleForPost($post)),
        ];
    }
}
