<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Post;
use App\Domain\ValueObject\PostReactionType;

interface PostRepositoryInterface
{
    public function save(Post $post): void;

    public function findVisibleById(int $id): ?Post;

    /**
     * @return list<Post>
     */
    public function paginateVisible(int $page, int $limit): array;

    public function countVisible(): int;

    public function countVisibleComments(Post $post): int;

    public function countReactions(Post $post, PostReactionType $type): int;
}
