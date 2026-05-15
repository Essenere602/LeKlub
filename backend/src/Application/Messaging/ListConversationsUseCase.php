<?php

declare(strict_types=1);

namespace App\Application\Messaging;

use App\Domain\Entity\User;
use App\Domain\Repository\ConversationRepositoryInterface;

final class ListConversationsUseCase
{
    public function __construct(
        private readonly ConversationRepositoryInterface $conversations,
        private readonly MessagingPresenter $presenter,
    ) {
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function execute(User $user): array
    {
        return array_map(
            fn ($conversation): array => $this->presenter->conversation($conversation, $user),
            $this->conversations->findForUser($user)
        );
    }
}
