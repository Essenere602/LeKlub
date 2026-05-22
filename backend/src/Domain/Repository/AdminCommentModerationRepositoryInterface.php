<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Comment;
use App\Domain\ValueObject\AdminContentStatus;

interface AdminCommentModerationRepositoryInterface
{
    /**
     * @return list<Comment>
     */
    public function paginateForModeration(AdminContentStatus $status, ?string $query, int $page, int $limit): array;

    public function countForModeration(AdminContentStatus $status, ?string $query): int;
}
