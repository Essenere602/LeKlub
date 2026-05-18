<?php

declare(strict_types=1);

namespace App\Application\Admin;

use App\Application\Feed\DeleteCommentUseCase;
use App\Domain\Entity\User;
use App\Domain\Exception\ResourceNotFoundException;
use App\Domain\Repository\CommentRepositoryInterface;

final class DeleteAdminCommentUseCase
{
    public function __construct(
        private readonly CommentRepositoryInterface $comments,
        private readonly DeleteCommentUseCase $deleteComment,
    ) {
    }

    public function execute(int $commentId, User $admin): void
    {
        $comment = $this->comments->findVisibleById($commentId);

        if ($comment === null) {
            throw new ResourceNotFoundException('Comment not found.');
        }

        $this->deleteComment->execute($comment, $admin);
    }
}
