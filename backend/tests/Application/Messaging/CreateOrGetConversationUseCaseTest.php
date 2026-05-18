<?php

declare(strict_types=1);

namespace App\Tests\Application\Messaging;

use App\Application\Messaging\CreateOrGetConversationUseCase;
use App\Application\Messaging\MessagingPresenter;
use App\Domain\Entity\Conversation;
use App\Domain\Entity\Message;
use App\Domain\Entity\User;
use App\Domain\Exception\MessagingException;
use App\Domain\Repository\ConversationRepositoryInterface;
use App\Domain\Repository\MessageRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\DTO\Messaging\CreateConversationRequest;
use App\Infrastructure\WebSocket\MessageNotifierInterface;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

final class CreateOrGetConversationUseCaseTest extends TestCase
{
    public function testItRejectsConversationWithSelf(): void
    {
        $sender = self::userWithId(1, 'sender@example.com', 'sender');
        $useCase = new CreateOrGetConversationUseCase(
            new MessagingUserRepository(),
            new MessagingConversationRepository(),
            new MessagingMessageRepository(),
            new MessagingPresenter(new MessagingMessageRepository()),
            new SpyMessageNotifier(),
        );

        $this->expectException(MessagingException::class);
        $this->expectExceptionMessage('CANNOT_MESSAGE_SELF');

        $useCase->execute($sender, CreateConversationRequest::fromArray([
            'recipientId' => 1,
        ]));
    }

    public function testItCreatesConversationWithFirstMessageAndNotification(): void
    {
        $sender = self::userWithId(1, 'sender@example.com', 'sender');
        $recipient = self::userWithId(2, 'recipient@example.com', 'recipient');
        $users = new MessagingUserRepository([$recipient]);
        $conversations = new MessagingConversationRepository();
        $messages = new MessagingMessageRepository();
        $notifier = new SpyMessageNotifier();

        $useCase = new CreateOrGetConversationUseCase(
            $users,
            $conversations,
            $messages,
            new MessagingPresenter($messages),
            $notifier,
        );

        $result = $useCase->execute($sender, CreateConversationRequest::fromArray([
            'recipientId' => 2,
            'firstMessage' => '  <b>Salut</b>  ',
        ]));

        self::assertNotNull($conversations->savedConversation);
        self::assertCount(1, $messages->savedMessages);
        self::assertSame('Salut', $messages->savedMessages[0]->getContent());
        self::assertSame($recipient, $notifier->recipient);
        self::assertSame('recipient', $result['participant']['username']);
    }

    private static function userWithId(int $id, string $email, string $username): User
    {
        $user = new User($email, $username, 'hash');
        $property = new ReflectionProperty(User::class, 'id');
        $property->setValue($user, $id);

        return $user;
    }
}

final class MessagingUserRepository implements UserRepositoryInterface
{
    /**
     * @param list<User> $users
     */
    public function __construct(private readonly array $users = [])
    {
    }

    public function findOneByEmail(string $email): ?User
    {
        return null;
    }

    public function findOneByUsername(string $username): ?User
    {
        return null;
    }

    public function findById(int $id): ?User
    {
        foreach ($this->users as $user) {
            if ($user->getId() === $id) {
                return $user;
            }
        }

        return null;
    }

    public function findForDirectory(User $currentUser, ?string $query, int $limit): array
    {
        return [];
    }

    public function paginateForAdmin(?string $query, int $page, int $limit): array
    {
        return [];
    }

    public function countForAdmin(?string $query): int
    {
        return count($this->users);
    }

    public function save(User $user): void
    {
    }
}

final class MessagingConversationRepository implements ConversationRepositoryInterface
{
    public ?Conversation $savedConversation = null;

    public function save(Conversation $conversation): void
    {
        $this->savedConversation = $conversation;
    }

    public function findById(int $id): ?Conversation
    {
        return null;
    }

    public function findBetweenUsers(User $first, User $second): ?Conversation
    {
        return null;
    }

    public function findForUser(User $user): array
    {
        return [];
    }

    public function countAll(): int
    {
        return $this->savedConversation === null ? 0 : 1;
    }
}

final class MessagingMessageRepository implements MessageRepositoryInterface
{
    /**
     * @var list<Message>
     */
    public array $savedMessages = [];

    public function save(Message $message): void
    {
        $this->savedMessages[] = $message;
    }

    public function findForConversation(Conversation $conversation): array
    {
        return [];
    }

    public function findVisibleForConversationAndUser(Conversation $conversation, User $user): array
    {
        return [];
    }

    public function markAsReadForRecipient(Conversation $conversation, User $recipient): void
    {
    }

    public function countUnreadForRecipient(Conversation $conversation, User $recipient): int
    {
        return 0;
    }

    public function countVisibleUnreadForRecipient(Conversation $conversation, User $recipient): int
    {
        return 0;
    }

    public function findLastForConversation(Conversation $conversation): ?Message
    {
        return $this->savedMessages[0] ?? null;
    }

    public function findLastVisibleForConversationAndUser(Conversation $conversation, User $user): ?Message
    {
        return $this->findLastForConversation($conversation);
    }

    public function findById(int $id): ?Message
    {
        foreach ($this->savedMessages as $message) {
            if ($message->getId() === $id) {
                return $message;
            }
        }

        return null;
    }

    public function countAll(): int
    {
        return count($this->savedMessages);
    }
}

final class SpyMessageNotifier implements MessageNotifierInterface
{
    public ?User $recipient = null;

    public function notifyNewMessage(User $recipient, Conversation $conversation, Message $message): void
    {
        $this->recipient = $recipient;
    }
}
