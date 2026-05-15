<?php

declare(strict_types=1);

namespace App\Application\Messaging;

use App\Domain\Entity\Conversation;
use App\Domain\Repository\MessageRepositoryInterface;

final class ListMessagesUseCase
{
    public function __construct(
        private readonly MessageRepositoryInterface $messages,
        private readonly MessagingPresenter $presenter,
    ) {
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function execute(Conversation $conversation): array
    {
        return array_map(
            fn ($message): array => $this->presenter->message($message),
            $this->messages->findForConversation($conversation)
        );
    }
}
