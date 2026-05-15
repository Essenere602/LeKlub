<?php

declare(strict_types=1);

namespace App\Application\Messaging;

use App\Domain\Entity\Conversation;
use App\Domain\Entity\Message;
use App\Domain\Entity\User;
use App\Domain\Repository\ConversationRepositoryInterface;
use App\Domain\Repository\MessageRepositoryInterface;
use App\DTO\Messaging\SendMessageRequest;
use App\Infrastructure\WebSocket\MessageNotifierInterface;

final class SendMessageUseCase
{
    public function __construct(
        private readonly MessageRepositoryInterface $messages,
        private readonly ConversationRepositoryInterface $conversations,
        private readonly MessagingPresenter $presenter,
        private readonly MessageNotifierInterface $notifier,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(Conversation $conversation, User $sender, SendMessageRequest $request): array
    {
        $message = new Message($conversation, $sender, $request->content);
        $this->messages->save($message);
        $this->conversations->save($conversation);

        $recipient = $conversation->otherParticipant($sender);
        if ($recipient !== null) {
            $this->notifier->notifyNewMessage($recipient, $conversation, $message);
        }

        return $this->presenter->message($message);
    }
}
