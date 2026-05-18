<?php

declare(strict_types=1);

namespace App\Application\Feed;

use App\Domain\Entity\Post;
use App\Domain\Exception\ResourceNotFoundException;
use App\Domain\Repository\PostRepositoryInterface;
use App\DTO\Feed\UpdatePostRequest;

final class UpdatePostUseCase
{
    public function __construct(
        private readonly PostRepositoryInterface $posts,
        private readonly FeedPresenter $presenter,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(Post $post, UpdatePostRequest $request): array
    {
        if ($post->isDeleted()) {
            throw ResourceNotFoundException::post();
        }

        $post->updateContent($request->content);
        $this->posts->save($post);

        return $this->presenter->post($post);
    }
}
