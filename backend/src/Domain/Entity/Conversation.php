<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Index(name: 'idx_conversation_participant_one', columns: ['participant_one_id'])]
#[ORM\Index(name: 'idx_conversation_participant_two', columns: ['participant_two_id'])]
class Conversation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $participantOne;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $participantTwo;

    #[ORM\Column]
    private DateTimeImmutable $createdAt;

    #[ORM\Column]
    private DateTimeImmutable $updatedAt;

    public function __construct(User $participantOne, User $participantTwo)
    {
        $now = new DateTimeImmutable();

        $this->participantOne = $participantOne;
        $this->participantTwo = $participantTwo;
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getParticipantOne(): User
    {
        return $this->participantOne;
    }

    public function getParticipantTwo(): User
    {
        return $this->participantTwo;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function includes(User $user): bool
    {
        return $user->getId() !== null
            && ($this->participantOne->getId() === $user->getId() || $this->participantTwo->getId() === $user->getId());
    }

    public function otherParticipant(User $user): ?User
    {
        if ($this->participantOne->getId() === $user->getId()) {
            return $this->participantTwo;
        }

        if ($this->participantTwo->getId() === $user->getId()) {
            return $this->participantOne;
        }

        return null;
    }

    public function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }
}
