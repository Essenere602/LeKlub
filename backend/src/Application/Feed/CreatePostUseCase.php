<?php

declare(strict_types=1);

namespace App\Application\Feed;

use App\Domain\Entity\Post;
use App\Domain\Entity\User;
use App\Domain\Repository\PostRepositoryInterface;
use App\DTO\Feed\CreatePostRequest;
use App\Application\User\SuspensionGuard;

final class CreatePostUseCase
{
    public function __construct(
        private readonly PostRepositoryInterface $posts,
        private readonly FeedPresenter $presenter,
        private readonly SuspensionGuard $suspensionGuard,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(User $author, CreatePostRequest $request): array
    {
        $this->suspensionGuard->assertCanWrite($author);

        $post = new Post($author, $request->content);

        $this->posts->save($post);

        return $this->presenter->post($post);
    }
}
