<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class UserProfile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'profile', targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(length: 80, nullable: true)]
    private ?string $displayName = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $bio = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $favoriteTeamName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $avatarUrl = null;

    #[ORM\Column]
    private DateTimeImmutable $createdAt;

    #[ORM\Column]
    private DateTimeImmutable $updatedAt;

    public function __construct(User $user)
    {
        $now = new DateTimeImmutable();

        $this->user = $user;
        $this->createdAt = $now;
        $this->updatedAt = $now;

        $user->setProfile($this);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function getFavoriteTeamName(): ?string
    {
        return $this->favoriteTeamName;
    }

    public function getAvatarUrl(): ?string
    {
        return $this->avatarUrl;
    }

    public function update(?string $displayName, ?string $bio, ?string $favoriteTeamName, ?string $avatarUrl): void
    {
        $this->displayName = $displayName;
        $this->bio = $bio;
        $this->favoriteTeamName = $favoriteTeamName;
        $this->avatarUrl = $avatarUrl;
        $this->touch();
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }
}
