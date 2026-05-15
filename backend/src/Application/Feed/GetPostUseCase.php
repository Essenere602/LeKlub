<?php

declare(strict_types=1);

namespace App\Application\Feed;

use App\Domain\Exception\ResourceNotFoundException;
use App\Domain\Repository\PostRepositoryInterface;

final class GetPostUseCase
{
    public function __construct(
        private readonly PostRepositoryInterface $posts,
        private readonly FeedPresenter $presenter,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(int $id): array
    {
        $post = $this->posts->findVisibleById($id);

        if ($post === null) {
            throw ResourceNotFoundException::post();
        }

        return $this->presenter->post($post);
    }
}
