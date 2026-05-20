<?php

declare(strict_types=1);

namespace App\Application\Admin;

use App\Domain\Entity\Comment;
use App\Domain\Entity\FeedReport;
use App\Domain\Entity\Post;
use App\Domain\Entity\User;
use App\Domain\Repository\PostRepositoryInterface;
use App\Domain\ValueObject\PostReactionType;

final class AdminPresenter
{
    public function __construct(
        private readonly PostRepositoryInterface $posts,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function user(User $user): array
    {
        $profile = $user->getProfile();

        return [
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'displayName' => $profile?->getDisplayName(),
            'avatarUrl' => $profile?->getAvatarUrl(),
            'roles' => $user->getRoles(),
            'createdAt' => $user->getCreatedAt()->format(DATE_ATOM),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function post(Post $post): array
    {
        return [
            'id' => $post->getId(),
            'content' => $post->getContent(),
            'author' => $this->author($post->getAuthor()),
            'likesCount' => $this->posts->countReactions($post, PostReactionType::Like),
            'dislikesCount' => $this->posts->countReactions($post, PostReactionType::Dislike),
            'commentsCount' => $this->posts->countVisibleComments($post),
            'createdAt' => $post->getCreatedAt()->format(DATE_ATOM),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function comment(Comment $comment): array
    {
        $post = $comment->getPost();

        return [
            'id' => $comment->getId(),
            'content' => $comment->getContent(),
            'author' => $this->author($comment->getAuthor()),
            'post' => [
                'id' => $post->getId(),
                'excerpt' => mb_substr($post->getContent(), 0, 120),
            ],
            'createdAt' => $comment->getCreatedAt()->format(DATE_ATOM),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function report(FeedReport $report): array
    {
        $post = $report->getPost();
        $comment = $report->getComment();
        $content = $post ?? $comment;
        $author = $content?->getAuthor();

        return [
            'id' => $report->getId(),
            'type' => $post !== null ? 'post' : 'comment',
            'reason' => $report->getReason()->value,
            'details' => $report->getDetails(),
            'status' => $report->getStatus()->value,
            'reporter' => $this->author($report->getReporter()),
            'content' => [
                'id' => $content?->getId(),
                'excerpt' => $content !== null ? mb_substr($content->getContent(), 0, 160) : null,
                'author' => $author !== null ? $this->author($author) : null,
                'deletedAt' => $content?->getDeletedAt()?->format(DATE_ATOM),
            ],
            'post' => $comment !== null ? [
                'id' => $comment->getPost()->getId(),
                'excerpt' => mb_substr($comment->getPost()->getContent(), 0, 120),
            ] : null,
            'createdAt' => $report->getCreatedAt()->format(DATE_ATOM),
            'resolvedAt' => $report->getResolvedAt()?->format(DATE_ATOM),
            'resolvedBy' => $report->getResolvedBy() !== null ? $this->author($report->getResolvedBy()) : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function author(User $author): array
    {
        $profile = $author->getProfile();

        return [
            'id' => $author->getId(),
            'username' => $author->getUsername(),
            'displayName' => $profile?->getDisplayName(),
            'avatarUrl' => $profile?->getAvatarUrl(),
        ];
    }
}
