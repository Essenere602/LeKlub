<?php

declare(strict_types=1);

namespace App\Application\Admin;

use App\Domain\Entity\Comment;
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
