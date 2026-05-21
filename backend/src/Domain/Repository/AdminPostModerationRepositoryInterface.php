<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Post;
use App\Domain\ValueObject\AdminContentStatus;

interface AdminPostModerationRepositoryInterface
{
    /**
     * @return list<Post>
     */
    public function paginateForModeration(AdminContentStatus $status, ?string $query, int $page, int $limit): array;

    public function countForModeration(AdminContentStatus $status, ?string $query): int;
}
