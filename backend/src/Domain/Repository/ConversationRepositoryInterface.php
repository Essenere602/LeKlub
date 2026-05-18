<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Conversation;
use App\Domain\Entity\User;

interface ConversationRepositoryInterface
{
    public function save(Conversation $conversation): void;

    public function findById(int $id): ?Conversation;

    public function findBetweenUsers(User $first, User $second): ?Conversation;

    /**
     * @return list<Conversation>
     */
    public function findForUser(User $user): array;

    public function countAll(): int;
}
