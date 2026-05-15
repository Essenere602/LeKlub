<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\WebSocket;

use App\Domain\Entity\Conversation;
use App\Domain\Entity\Message;
use App\Domain\Entity\User;
use App\Infrastructure\WebSocket\FileMessageNotifier;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

final class FileMessageNotifierTest extends TestCase
{
    public function testNotificationDoesNotExposeMessageContent(): void
    {
        $projectDir = sys_get_temp_dir().'/leklub-notifier-test-'.uniqid('', true);
        $sender = $this->userWithId(1, 'sender@example.com', 'sender');
        $recipient = $this->userWithId(2, 'recipient@example.com', 'recipient');
        $conversation = new Conversation($sender, $recipient);
        $message = new Message($conversation, $sender, 'private message content');

        $this->setId($conversation, Conversation::class, 10);
        $this->setId($message, Message::class, 20);

        $notifier = new FileMessageNotifier($projectDir);
        $notifier->notifyNewMessage($recipient, $conversation, $message);

        $notificationFile = $projectDir.'/var/websocket/notifications.jsonl';
        $payload = file_get_contents($notificationFile);

        self::assertIsString($payload);
        self::assertStringContainsString('"type":"new_message"', $payload);
        self::assertStringContainsString('"conversationId":10', $payload);
        self::assertStringContainsString('"id":20', $payload);
        self::assertStringNotContainsString('private message content', $payload);

        unlink($notificationFile);
        rmdir($projectDir.'/var/websocket');
        rmdir($projectDir.'/var');
        rmdir($projectDir);
    }

    private function userWithId(int $id, string $email, string $username): User
    {
        $user = new User($email, $username, 'hash');
        $this->setId($user, User::class, $id);

        return $user;
    }

    private function setId(object $object, string $className, int $id): void
    {
        $property = new ReflectionProperty($className, 'id');
        $property->setValue($object, $id);
    }
}
