<?php

declare(strict_types=1);

namespace App\Application\Messaging;

use App\Domain\Entity\Conversation;
use App\Domain\Entity\Message;
use App\Domain\Entity\User;
use App\Domain\Repository\MessageRepositoryInterface;

final class MessagingPresenter
{
    public function __construct(
        private readonly MessageRepositoryInterface $messages,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function conversation(Conversation $conversation, User $currentUser): array
    {
        $other = $conversation->otherParticipant($currentUser);
        $lastMessage = $this->messages->findLastForConversation($conversation);

        return [
            'id' => $conversation->getId(),
            'participant' => $other === null ? null : [
                'id' => $other->getId(),
                'username' => $other->getUsername(),
            ],
            'lastMessage' => $lastMessage === null ? null : $this->message($lastMessage),
            'unreadCount' => $this->messages->countUnreadForRecipient($conversation, $currentUser),
            'updatedAt' => $conversation->getUpdatedAt()->format(DATE_ATOM),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function message(Message $message): array
    {
        $sender = $message->getSender();

        return [
            'id' => $message->getId(),
            'content' => $message->getContent(),
            'sender' => [
                'id' => $sender->getId(),
                'username' => $sender->getUsername(),
            ],
            'readAt' => $message->getReadAt()?->format(DATE_ATOM),
            'createdAt' => $message->getCreatedAt()->format(DATE_ATOM),
        ];
    }
}
