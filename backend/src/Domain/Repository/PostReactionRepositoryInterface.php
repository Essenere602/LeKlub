<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Post;
use App\Domain\Entity\PostReaction;
use App\Domain\Entity\User;

interface PostReactionRepositoryInterface
{
    public function save(PostReaction $reaction): void;

    public function remove(PostReaction $reaction): void;

    public function findForUserAndPost(User $user, Post $post): ?PostReaction;
}
