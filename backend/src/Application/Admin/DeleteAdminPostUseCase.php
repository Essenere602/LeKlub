<?php

declare(strict_types=1);

namespace App\Application\Admin;

use App\Application\Feed\DeletePostUseCase;
use App\Domain\Entity\User;
use App\Domain\Exception\ResourceNotFoundException;
use App\Domain\Repository\PostRepositoryInterface;

final class DeleteAdminPostUseCase
{
    public function __construct(
        private readonly PostRepositoryInterface $posts,
        private readonly DeletePostUseCase $deletePost,
    ) {
    }

    public function execute(int $postId, User $admin): void
    {
        $post = $this->posts->findVisibleById($postId);

        if ($post === null) {
            throw new ResourceNotFoundException('Post not found.');
        }

        $this->deletePost->execute($post, $admin);
    }
}
