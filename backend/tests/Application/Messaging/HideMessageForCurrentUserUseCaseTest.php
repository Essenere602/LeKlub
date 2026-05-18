<?php

declare(strict_types=1);

namespace App\Tests\Application\Messaging;

use App\Application\Messaging\HideMessageForCurrentUserUseCase;
use App\Application\Messaging\ListConversationsUseCase;
use App\Application\Messaging\ListMessagesUseCase;
use App\Application\Messaging\MessagingPresenter;
use App\Domain\Entity\Conversation;
use App\Domain\Entity\Message;
use App\Domain\Entity\MessageHiddenForUser;
use App\Domain\Entity\User;
use App\Domain\Exception\ResourceNotFoundException;
use App\Domain\Repository\ConversationRepositoryInterface;
use App\Domain\Repository\MessageHiddenForUserRepositoryInterface;
use App\Domain\Repository\MessageRepositoryInterface;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

final class HideMessageForCurrentUserUseCaseTest extends TestCase
{
    public function testItHidesMessageForCurrentUserOnly(): void
    {
        $alice = self::userWithId(1, 'alice@example.com', 'alice');
        $bob = self::userWithId(2, 'bob@example.com', 'bob');
        $conversation = new Conversation($alice, $bob);
        $message = self::messageWithId(10, new Message($conversation, $alice, 'Secret'));
        $messages = new HideMessageRepository([$message]);
        $hidden = new InMemoryHiddenMessageRepository();
        $useCase = new HideMessageForCurrentUserUseCase($messages, $hidden);

        $useCase->execute($conversation, 10, $alice);

        self::assertCount(1, $hidden->hiddenMessages);
        self::assertSame($message, $hidden->hiddenMessages[0]->getMessage());
        self::assertSame($alice, $hidden->hiddenMessages[0]->getUser());
        self::assertSame([], $messages->findVisibleForConversationAndUser($conversation, $alice));
        self::assertSame([$message], $messages->findVisibleForConversationAndUser($conversation, $bob));
    }

    public function testItIsIdempotent(): void
    {
        $alice = self::userWithId(1, 'alice@example.com', 'alice');
        $bob = self::userWithId(2, 'bob@example.com', 'bob');
        $conversation = new Conversation($alice, $bob);
        $message = self::messageWithId(10, new Message($conversation, $alice, 'Secret'));
        $messages = new HideMessageRepository([$message]);
        $hidden = new InMemoryHiddenMessageRepository();
        $useCase = new HideMessageForCurrentUserUseCase($messages, $hidden);

        $useCase->execute($conversation, 10, $alice);
        $useCase->execute($conversation, 10, $alice);

        self::assertCount(1, $hidden->hiddenMessages);
    }

    public function testItRejectsMessageFromAnotherConversation(): void
    {
        $alice = self::userWithId(1, 'alice@example.com', 'alice');
        $bob = self::userWithId(2, 'bob@example.com', 'bob');
        $other = self::userWithId(3, 'other@example.com', 'other');
        $conversation = new Conversation($alice, $bob);
        $otherConversation = new Conversation($alice, $other);
        $message = self::messageWithId(10, new Message($otherConversation, $alice, 'Secret'));
        $useCase = new HideMessageForCurrentUserUseCase(
            new HideMessageRepository([$message]),
            new InMemoryHiddenMessageRepository(),
        );

        $this->expectException(ResourceNotFoundException::class);

        $useCase->execute($conversation, 10, $alice);
    }

    public function testListMessagesReturnsOnlyVisibleMessagesForCurrentUser(): void
    {
        $alice = self::userWithId(1, 'alice@example.com', 'alice');
        $bob = self::userWithId(2, 'bob@example.com', 'bob');
        $conversation = new Conversation($alice, $bob);
        $first = self::messageWithId(10, new Message($conversation, $alice, 'Visible'));
        $second = self::messageWithId(11, new Message($conversation, $bob, 'Hidden'));
        $messages = new HideMessageRepository([$first, $second]);
        $hidden = new InMemoryHiddenMessageRepository();
        $useCase = new HideMessageForCurrentUserUseCase($messages, $hidden);
        $listMessages = new ListMessagesUseCase($messages, new MessagingPresenter($messages));

        $useCase->execute($conversation, 11, $alice);

        $result = $listMessages->execute($conversation, $alice);

        self::assertCount(1, $result);
        self::assertSame('Visible', $result[0]['content']);
    }

    public function testListConversationsExcludesConversationWithoutVisibleMessageForCurrentUser(): void
    {
        $alice = self::userWithId(1, 'alice@example.com', 'alice');
        $bob = self::userWithId(2, 'bob@example.com', 'bob');
        $conversation = new Conversation($alice, $bob);
        $message = self::messageWithId(10, new Message($conversation, $bob, 'Hidden for Alice'));
        $messages = new HideMessageRepository([$message]);
        $hidden = new InMemoryHiddenMessageRepository();
        $hideMessage = new HideMessageForCurrentUserUseCase($messages, $hidden);
        $listConversations = new ListConversationsUseCase(
            new InMemoryConversationRepository([$conversation]),
            $messages,
            new MessagingPresenter($messages),
        );

        $hideMessage->execute($conversation, 10, $alice);

        self::assertSame([], $listConversations->execute($alice));
        self::assertCount(1, $listConversations->execute($bob));
    }

    private static function userWithId(int $id, string $email, string $username): User
    {
        $user = new User($email, $username, 'hash');
        $property = new ReflectionProperty(User::class, 'id');
        $property->setValue($user, $id);

        return $user;
    }

    private static function messageWithId(int $id, Message $message): Message
    {
        $property = new ReflectionProperty(Message::class, 'id');
        $property->setValue($message, $id);

        return $message;
    }
}

final class HideMessageRepository implements MessageRepositoryInterface
{
    /**
     * @var list<Message>
     */
    public array $messages;

    /**
     * @var list<MessageHiddenForUser>
     */
    public static array $hiddenMessages = [];

    /**
     * @param list<Message> $messages
     */
    public function __construct(array $messages)
    {
        $this->messages = $messages;
        self::$hiddenMessages = [];
    }

    public function save(Message $message): void
    {
        $this->messages[] = $message;
    }

    public function findForConversation(Conversation $conversation): array
    {
        return array_values(array_filter(
            $this->messages,
            fn (Message $message): bool => $message->getConversation() === $conversation
        ));
    }

    public function findVisibleForConversationAndUser(Conversation $conversation, User $user): array
    {
        return array_values(array_filter(
            $this->findForConversation($conversation),
            fn (Message $message): bool => !$this->isHiddenForUser($message, $user)
        ));
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
        return array_reverse($this->findForConversation($conversation))[0] ?? null;
    }

    public function findLastVisibleForConversationAndUser(Conversation $conversation, User $user): ?Message
    {
        return array_reverse($this->findVisibleForConversationAndUser($conversation, $user))[0] ?? null;
    }

    public function findById(int $id): ?Message
    {
        foreach ($this->messages as $message) {
            if ($message->getId() === $id) {
                return $message;
            }
        }

        return null;
    }

    public function countAll(): int
    {
        return count($this->messages);
    }

    private function isHiddenForUser(Message $message, User $user): bool
    {
        foreach (self::$hiddenMessages as $hiddenMessage) {
            if ($hiddenMessage->getMessage() === $message && $hiddenMessage->getUser() === $user) {
                return true;
            }
        }

        return false;
    }
}

final class InMemoryConversationRepository implements ConversationRepositoryInterface
{
    /**
     * @param list<Conversation> $conversations
     */
    public function __construct(private readonly array $conversations)
    {
    }

    public function save(Conversation $conversation): void
    {
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
        return $this->conversations;
    }

    public function countAll(): int
    {
        return count($this->conversations);
    }
}

final class InMemoryHiddenMessageRepository implements MessageHiddenForUserRepositoryInterface
{
    /**
     * @var list<MessageHiddenForUser>
     */
    public array $hiddenMessages = [];

    public function findForMessageAndUser(Message $message, User $user): ?MessageHiddenForUser
    {
        foreach ($this->hiddenMessages as $hiddenMessage) {
            if ($hiddenMessage->getMessage() === $message && $hiddenMessage->getUser() === $user) {
                return $hiddenMessage;
            }
        }

        return null;
    }

    public function save(MessageHiddenForUser $hiddenMessage): void
    {
        $this->hiddenMessages[] = $hiddenMessage;
        HideMessageRepository::$hiddenMessages = $this->hiddenMessages;
    }
}
