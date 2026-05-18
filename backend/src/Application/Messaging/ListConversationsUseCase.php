<?php

declare(strict_types=1);

namespace App\Application\Messaging;

use App\Domain\Entity\User;
use App\Domain\Repository\ConversationRepositoryInterface;
use App\Domain\Repository\MessageRepositoryInterface;

final class ListConversationsUseCase
{
    public function __construct(
        private readonly ConversationRepositoryInterface $conversations,
        private readonly MessageRepositoryInterface $messages,
        private readonly MessagingPresenter $presenter,
    ) {
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function execute(User $user): array
    {
        $visibleConversations = array_filter(
            $this->conversations->findForUser($user),
            fn ($conversation): bool => $this->messages->findLastVisibleForConversationAndUser($conversation, $user) !== null
        );

        return array_values(array_map(
            fn ($conversation): array => $this->presenter->conversation($conversation, $user),
            $visibleConversations
        ));
    }
}
