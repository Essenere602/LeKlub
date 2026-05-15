<?php

declare(strict_types=1);

namespace App\Application\Feed;

use App\Domain\Entity\Comment;
use App\Domain\Entity\User;
use App\Domain\Exception\ResourceNotFoundException;
use App\Domain\Repository\CommentRepositoryInterface;
use App\Domain\Repository\PostRepositoryInterface;
use App\DTO\Feed\CreateCommentRequest;

final class AddCommentUseCase
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
    public function execute(int $postId, User $author, CreateCommentRequest $request): array
    {
        $post = $this->posts->findVisibleById($postId);

        if ($post === null) {
            throw ResourceNotFoundException::post();
        }

        $comment = new Comment($post, $author, $request->content);
        $this->comments->save($comment);

        return $this->presenter->comment($comment);
    }
}
