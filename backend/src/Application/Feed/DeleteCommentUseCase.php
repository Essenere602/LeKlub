<?php

declare(strict_types=1);

namespace App\Application\Feed;

use App\Domain\Entity\Comment;
use App\Domain\Entity\User;
use App\Domain\Repository\CommentRepositoryInterface;

final class DeleteCommentUseCase
{
    public function __construct(
        private readonly CommentRepositoryInterface $comments,
    ) {
    }

    public function execute(Comment $comment, User $deletedBy): void
    {
        $comment->delete($deletedBy);

        $this->comments->save($comment);
    }
}
