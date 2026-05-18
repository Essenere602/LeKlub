<?php

declare(strict_types=1);

namespace App\Application\Admin;

use App\Domain\Repository\CommentRepositoryInterface;
use App\Domain\Repository\ConversationRepositoryInterface;
use App\Domain\Repository\MessageRepositoryInterface;
use App\Domain\Repository\PostRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;

final class GetAdminOverviewUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly PostRepositoryInterface $posts,
        private readonly CommentRepositoryInterface $comments,
        private readonly ConversationRepositoryInterface $conversations,
        private readonly MessageRepositoryInterface $messages,
    ) {
    }

    /**
     * @return array<string, int>
     */
    public function execute(): array
    {
        return [
            'usersCount' => $this->users->countForAdmin(null),
            'postsCount' => $this->posts->countVisible(),
            'commentsCount' => $this->comments->countVisible(),
            'conversationsCount' => $this->conversations->countAll(),
            'messagesCount' => $this->messages->countAll(),
        ];
    }
}
