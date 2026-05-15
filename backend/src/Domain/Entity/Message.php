<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Index(name: 'idx_message_conversation_created', columns: ['conversation_id', 'created_at'])]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Conversation::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Conversation $conversation;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $sender;

    #[ORM\Column(type: 'text')]
    private string $content;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $readAt = null;

    #[ORM\Column]
    private DateTimeImmutable $createdAt;

    public function __construct(Conversation $conversation, User $sender, string $content)
    {
        $this->conversation = $conversation;
        $this->sender = $sender;
        $this->content = $content;
        $this->createdAt = new DateTimeImmutable();
        $conversation->touch();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getConversation(): Conversation
    {
        return $this->conversation;
    }

    public function getSender(): User
    {
        return $this->sender;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getReadAt(): ?DateTimeImmutable
    {
        return $this->readAt;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function markAsRead(): void
    {
        $this->readAt ??= new DateTimeImmutable();
    }
}
