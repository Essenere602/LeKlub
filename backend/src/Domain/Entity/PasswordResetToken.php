<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'password_reset_token')]
#[ORM\Index(name: 'idx_password_reset_token_hash', columns: ['token_hash'])]
#[ORM\Index(name: 'idx_password_reset_token_user', columns: ['user_id'])]
#[ORM\Index(name: 'idx_password_reset_token_expires_at', columns: ['expires_at'])]
class PasswordResetToken
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(name: 'token_hash', length: 64)]
    private string $tokenHash;

    #[ORM\Column]
    private DateTimeImmutable $expiresAt;

    #[ORM\Column]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $usedAt = null;

    public function __construct(User $user, string $tokenHash, DateTimeImmutable $expiresAt)
    {
        $this->user = $user;
        $this->tokenHash = $tokenHash;
        $this->expiresAt = $expiresAt;
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getTokenHash(): string
    {
        return $this->tokenHash;
    }

    public function getExpiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUsedAt(): ?DateTimeImmutable
    {
        return $this->usedAt;
    }

    public function isUsable(): bool
    {
        return $this->usedAt === null && $this->expiresAt > new DateTimeImmutable();
    }

    public function markAsUsed(): void
    {
        if ($this->usedAt !== null) {
            return;
        }

        $this->usedAt = new DateTimeImmutable();
    }
}
