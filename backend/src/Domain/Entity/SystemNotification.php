<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\ValueObject\SystemNotificationType;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'system_notification')]
#[ORM\Index(name: 'idx_system_notification_recipient', columns: ['recipient_id'])]
#[ORM\Index(name: 'idx_system_notification_read_at', columns: ['read_at'])]
#[ORM\Index(name: 'idx_system_notification_created_at', columns: ['created_at'])]
class SystemNotification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $recipient;

    #[ORM\Column(length: 40, enumType: SystemNotificationType::class)]
    private SystemNotificationType $type;

    #[ORM\Column(length: 120)]
    private string $title;

    #[ORM\Column(type: 'text')]
    private string $message;

    #[ORM\Column]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $readAt = null;

    public function __construct(User $recipient, SystemNotificationType $type, string $title, string $message)
    {
        $this->recipient = $recipient;
        $this->type = $type;
        $this->title = $title;
        $this->message = $message;
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRecipient(): User
    {
        return $this->recipient;
    }

    public function getType(): SystemNotificationType
    {
        return $this->type;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getReadAt(): ?DateTimeImmutable
    {
        return $this->readAt;
    }

    public function markAsRead(): void
    {
        if ($this->readAt !== null) {
            return;
        }

        $this->readAt = new DateTimeImmutable();
    }
}
