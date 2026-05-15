<?php

declare(strict_types=1);

namespace App\Infrastructure\WebSocket;

use App\Domain\Entity\Conversation;
use App\Domain\Entity\Message;
use App\Domain\Entity\User;

final class FileMessageNotifier implements MessageNotifierInterface
{
    public function __construct(
        private readonly string $projectDir,
    ) {
    }

    public function notifyNewMessage(User $recipient, Conversation $conversation, Message $message): void
    {
        if ($recipient->getId() === null) {
            return;
        }

        $directory = $this->projectDir.'/var/websocket';
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $payload = [
            'type' => 'new_message',
            'recipientId' => $recipient->getId(),
            'conversationId' => $conversation->getId(),
            'message' => [
                'id' => $message->getId(),
                'sender' => [
                    'id' => $message->getSender()->getId(),
                    'username' => $message->getSender()->getUsername(),
                ],
                'createdAt' => $message->getCreatedAt()->format(DATE_ATOM),
            ],
        ];

        file_put_contents($directory.'/notifications.jsonl', json_encode($payload, JSON_THROW_ON_ERROR).PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}
