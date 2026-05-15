<?php

declare(strict_types=1);

namespace App\Application\Messaging;

use App\Domain\Entity\Conversation;
use App\Domain\Entity\Message;
use App\Domain\Entity\User;
use App\Domain\Exception\MessagingException;
use App\Domain\Exception\ResourceNotFoundException;
use App\Domain\Repository\ConversationRepositoryInterface;
use App\Domain\Repository\MessageRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\DTO\Messaging\CreateConversationRequest;
use App\Infrastructure\WebSocket\MessageNotifierInterface;

final class CreateOrGetConversationUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly ConversationRepositoryInterface $conversations,
        private readonly MessageRepositoryInterface $messages,
        private readonly MessagingPresenter $presenter,
        private readonly MessageNotifierInterface $notifier,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(User $sender, CreateConversationRequest $request): array
    {
        if ($sender->getId() === $request->recipientId) {
            throw MessagingException::cannotMessageSelf();
        }

        $recipient = $this->users->findById($request->recipientId);

        if (!$recipient instanceof User) {
            throw ResourceNotFoundException::user();
        }

        $conversation = $this->conversations->findBetweenUsers($sender, $recipient)
            ?? new Conversation($sender, $recipient);

        $this->conversations->save($conversation);

        if ($request->firstMessage !== null) {
            $message = new Message($conversation, $sender, $request->firstMessage);
            $this->messages->save($message);
            $this->notifier->notifyNewMessage($recipient, $conversation, $message);
        }

        return $this->presenter->conversation($conversation, $sender);
    }
}
