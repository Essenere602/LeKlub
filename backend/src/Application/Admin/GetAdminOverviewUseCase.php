<?php

declare(strict_types=1);

namespace App\Application\Admin;

use App\Domain\Repository\CommentRepositoryInterface;
use App\Domain\Repository\ConversationRepositoryInterface;
use App\Domain\Repository\FeedReportRepositoryInterface;
use App\Domain\Repository\MessageRepositoryInterface;
use App\Domain\Repository\AdminCommentModerationRepositoryInterface;
use App\Domain\Repository\AdminPostModerationRepositoryInterface;
use App\Domain\Repository\AdminUserStatsRepositoryInterface;
use App\Domain\Repository\PostRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\Repository\UserWarningRepositoryInterface;
use App\Domain\ValueObject\AdminContentStatus;
use App\Domain\ValueObject\FeedReportStatus;

final class GetAdminOverviewUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly PostRepositoryInterface $posts,
        private readonly CommentRepositoryInterface $comments,
        private readonly ConversationRepositoryInterface $conversations,
        private readonly MessageRepositoryInterface $messages,
        private readonly FeedReportRepositoryInterface $reports,
        private readonly UserWarningRepositoryInterface $warnings,
        private readonly AdminPostModerationRepositoryInterface $adminPosts,
        private readonly AdminCommentModerationRepositoryInterface $adminComments,
        private readonly AdminUserStatsRepositoryInterface $adminUsers,
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
            'openReportsCount' => $this->reports->countForAdmin(FeedReportStatus::Open),
            'conversationsCount' => $this->conversations->countAll(),
            'messagesCount' => $this->messages->countAll(),
            'deletedPostsCount' => $this->adminPosts->countForModeration(AdminContentStatus::Deleted, null),
            'deletedCommentsCount' => $this->adminComments->countForModeration(AdminContentStatus::Deleted, null),
            'suspendedUsersCount' => $this->adminUsers->countSuspendedUsers(),
            'warningsCount' => $this->warnings->countForAdmin(null, false),
        ];
    }
}
