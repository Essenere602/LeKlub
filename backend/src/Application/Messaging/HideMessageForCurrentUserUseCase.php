<?php

declare(strict_types=1);

namespace App\Application\Messaging;

use App\Domain\Entity\Conversation;
use App\Domain\Entity\MessageHiddenForUser;
use App\Domain\Entity\User;
use App\Domain\Exception\ResourceNotFoundException;
use App\Domain\Repository\MessageHiddenForUserRepositoryInterface;
use App\Domain\Repository\MessageRepositoryInterface;

final class HideMessageForCurrentUserUseCase
{
    public function __construct(
        private readonly MessageRepositoryInterface $messages,
        private readonly MessageHiddenForUserRepositoryInterface $hiddenMessages,
    ) {
    }

    public function execute(Conversation $conversation, int $messageId, User $currentUser): void
    {
        $message = $this->messages->findById($messageId);

        if ($message === null || $message->getConversation() !== $conversation) {
            throw ResourceNotFoundException::message();
        }

        if ($this->hiddenMessages->findForMessageAndUser($message, $currentUser) !== null) {
            return;
        }

        $this->hiddenMessages->save(new MessageHiddenForUser($message, $currentUser));
    }
}
