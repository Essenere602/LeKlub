<?php

declare(strict_types=1);

namespace App\Application\Feed;

use App\Domain\Entity\User;
use App\Domain\Exception\ResourceNotFoundException;
use App\Domain\Repository\PostReactionRepositoryInterface;
use App\Domain\Repository\PostRepositoryInterface;

final class RemovePostReactionUseCase
{
    public function __construct(
        private readonly PostRepositoryInterface $posts,
        private readonly PostReactionRepositoryInterface $reactions,
        private readonly FeedPresenter $presenter,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(int $postId, User $user): array
    {
        $post = $this->posts->findVisibleById($postId);

        if ($post === null) {
            throw ResourceNotFoundException::post();
        }

        $reaction = $this->reactions->findForUserAndPost($user, $post);

        if ($reaction !== null) {
            $this->reactions->remove($reaction);
        }

        return $this->presenter->post($post);
    }
}
