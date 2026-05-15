<?php

declare(strict_types=1);

namespace App\Application\Feed;

use App\Domain\Entity\PostReaction;
use App\Domain\Entity\User;
use App\Domain\Exception\ResourceNotFoundException;
use App\Domain\Repository\PostReactionRepositoryInterface;
use App\Domain\Repository\PostRepositoryInterface;
use App\Domain\ValueObject\PostReactionType;
use App\DTO\Feed\ReactToPostRequest;

final class ReactToPostUseCase
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
    public function execute(int $postId, User $user, ReactToPostRequest $request): array
    {
        $post = $this->posts->findVisibleById($postId);

        if ($post === null) {
            throw ResourceNotFoundException::post();
        }

        $type = PostReactionType::from($request->type);
        $reaction = $this->reactions->findForUserAndPost($user, $post);

        if ($reaction === null) {
            $reaction = new PostReaction($post, $user, $type);
        } else {
            $reaction->changeType($type);
        }

        $this->reactions->save($reaction);

        return $this->presenter->post($post);
    }
}
