<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'message_hidden_for_user')]
#[ORM\UniqueConstraint(name: 'uniq_message_hidden_user', columns: ['message_id', 'user_id'])]
#[ORM\Index(name: 'idx_message_hidden_message', columns: ['message_id'])]
#[ORM\Index(name: 'idx_message_hidden_user', columns: ['user_id'])]
class MessageHiddenForUser
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Message::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Message $message;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column]
    private DateTimeImmutable $hiddenAt;

    public function __construct(Message $message, User $user)
    {
        $this->message = $message;
        $this->user = $user;
        $this->hiddenAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMessage(): Message
    {
        return $this->message;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getHiddenAt(): DateTimeImmutable
    {
        return $this->hiddenAt;
    }
}
