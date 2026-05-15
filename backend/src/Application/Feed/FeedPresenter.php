<?php

declare(strict_types=1);

namespace App\Application\Feed;

use App\Domain\Entity\Comment;
use App\Domain\Entity\Post;
use App\Domain\Repository\PostRepositoryInterface;
use App\Domain\ValueObject\PostReactionType;

final class FeedPresenter
{
    public function __construct(
        private readonly PostRepositoryInterface $posts,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function post(Post $post): array
    {
        $author = $post->getAuthor();

        return [
            'id' => $post->getId(),
            'content' => $post->getContent(),
            'author' => [
                'id' => $author->getId(),
                'username' => $author->getUsername(),
            ],
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
        $author = $comment->getAuthor();

        return [
            'id' => $comment->getId(),
            'content' => $comment->getContent(),
            'author' => [
                'id' => $author->getId(),
                'username' => $author->getUsername(),
            ],
            'createdAt' => $comment->getCreatedAt()->format(DATE_ATOM),
        ];
    }
}
