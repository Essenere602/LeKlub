<?php

declare(strict_types=1);

namespace App\Application\Feed;

use App\Domain\Entity\Post;
use App\Domain\Entity\User;
use App\Domain\Repository\PostRepositoryInterface;

final class DeletePostUseCase
{
    public function __construct(
        private readonly PostRepositoryInterface $posts,
    ) {
    }

    public function execute(Post $post, User $deletedBy): void
    {
        $post->delete($deletedBy);

        $this->posts->save($post);
    }
}
