<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Conversation;
use App\Domain\Entity\Message;
use App\Domain\Entity\User;

interface MessageRepositoryInterface
{
    public function save(Message $message): void;

    /**
     * @return list<Message>
     */
    public function findForConversation(Conversation $conversation): array;

    /**
     * @return list<Message>
     */
    public function findVisibleForConversationAndUser(Conversation $conversation, User $user): array;

    public function markAsReadForRecipient(Conversation $conversation, User $recipient): void;

    public function countUnreadForRecipient(Conversation $conversation, User $recipient): int;

    public function countVisibleUnreadForRecipient(Conversation $conversation, User $recipient): int;

    public function findLastForConversation(Conversation $conversation): ?Message;

    public function findLastVisibleForConversationAndUser(Conversation $conversation, User $user): ?Message;

    public function findById(int $id): ?Message;

    public function countAll(): int;
}
