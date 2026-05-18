<?php

declare(strict_types=1);

namespace App\Application\Feed;

use App\Domain\Entity\Comment;
use App\Domain\Exception\ResourceNotFoundException;
use App\Domain\Repository\CommentRepositoryInterface;
use App\DTO\Feed\UpdateCommentRequest;

final class UpdateCommentUseCase
{
    public function __construct(
        private readonly CommentRepositoryInterface $comments,
        private readonly FeedPresenter $presenter,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(Comment $comment, UpdateCommentRequest $request): array
    {
        if ($comment->isDeleted() || $comment->getPost()->isDeleted()) {
            throw ResourceNotFoundException::comment();
        }

        $comment->updateContent($request->content);
        $this->comments->save($comment);

        return $this->presenter->comment($comment);
    }
}
