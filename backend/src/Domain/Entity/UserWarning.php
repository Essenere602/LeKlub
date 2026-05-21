<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'user_warning')]
#[ORM\Index(name: 'idx_user_warning_user', columns: ['user_id'])]
#[ORM\Index(name: 'idx_user_warning_created_at', columns: ['created_at'])]
class UserWarning
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\ManyToOne(targetEntity: FeedReport::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private FeedReport $report;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $createdBy;

    #[ORM\Column(length: 40)]
    private string $contentType;

    #[ORM\Column]
    private int $contentId;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $reason;

    #[ORM\Column]
    private DateTimeImmutable $createdAt;

    public function __construct(User $user, FeedReport $report, User $createdBy, string $contentType, int $contentId, ?string $reason)
    {
        $this->user = $user;
        $this->report = $report;
        $this->createdBy = $createdBy;
        $this->contentType = $contentType;
        $this->contentId = $contentId;
        $this->reason = $reason;
        $this->createdAt = new DateTimeImmutable();
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
