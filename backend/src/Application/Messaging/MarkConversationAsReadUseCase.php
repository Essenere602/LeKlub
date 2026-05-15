<?php

declare(strict_types=1);

namespace App\Application\Messaging;

use App\Domain\Entity\Conversation;
use App\Domain\Entity\User;
use App\Domain\Repository\MessageRepositoryInterface;

final class MarkConversationAsReadUseCase
{
    public function __construct(
        private readonly MessageRepositoryInterface $messages,
    ) {
    }

    public function execute(Conversation $conversation, User $reader): void
    {
        $this->messages->markAsReadForRecipient($conversation, $reader);
    }
}
