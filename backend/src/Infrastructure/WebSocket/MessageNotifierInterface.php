<?php

declare(strict_types=1);

namespace App\Infrastructure\WebSocket;

use App\Domain\Entity\Conversation;
use App\Domain\Entity\Message;
use App\Domain\Entity\User;

interface MessageNotifierInterface
{
    public function notifyNewMessage(User $recipient, Conversation $conversation, Message $message): void;
}
