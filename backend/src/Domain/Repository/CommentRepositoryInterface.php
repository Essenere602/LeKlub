<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Comment;
use App\Domain\Entity\Post;

interface CommentRepositoryInterface
{
    public function save(Comment $comment): void;

    public function findVisibleById(int $id): ?Comment;

    /**
     * @return list<Comment>
     */
    public function paginateVisibleForPost(Post $post, int $page, int $limit): array;

    public function countVisibleForPost(Post $post): int;

    /**
     * @return list<Comment>
     */
    public function paginateVisibleForAdmin(int $page, int $limit): array;

    public function countVisible(): int;
}
