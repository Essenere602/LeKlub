<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\ValueObject\FeedReportDecision;
use App\Domain\ValueObject\FeedReportReason;
use App\Domain\ValueObject\FeedReportStatus;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'feed_report')]
#[ORM\UniqueConstraint(name: 'uniq_feed_report_reporter_post', columns: ['reporter_id', 'post_id'])]
#[ORM\UniqueConstraint(name: 'uniq_feed_report_reporter_comment', columns: ['reporter_id', 'comment_id'])]
#[ORM\Index(name: 'idx_feed_report_status', columns: ['status'])]
#[ORM\Index(name: 'idx_feed_report_created_at', columns: ['created_at'])]
#[ORM\Index(name: 'idx_feed_report_post', columns: ['post_id'])]
#[ORM\Index(name: 'idx_feed_report_comment', columns: ['comment_id'])]
class FeedReport
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $reporter;

    #[ORM\ManyToOne(targetEntity: Post::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?Post $post = null;

    #[ORM\ManyToOne(targetEntity: Comment::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?Comment $comment = null;

    #[ORM\Column(length: 40, enumType: FeedReportReason::class)]
    private FeedReportReason $reason;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $details = null;

    #[ORM\Column(length: 20, enumType: FeedReportStatus::class)]
    private FeedReportStatus $status = FeedReportStatus::Open;

    #[ORM\Column(length: 40, nullable: true, enumType: FeedReportDecision::class)]
    private ?FeedReportDecision $decision = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $adminNote = null;

    #[ORM\Column]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $resolvedAt = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $resolvedBy = null;

    private function __construct(User $reporter, FeedReportReason $reason, ?string $details)
    {
        $this->reporter = $reporter;
        $this->reason = $reason;
        $this->details = $details;
        $this->createdAt = new DateTimeImmutable();
    }

    public static function forPost(User $reporter, Post $post, FeedReportReason $reason, ?string $details): self
    {
        $report = new self($reporter, $reason, $details);
        $report->post = $post;

        return $report;
    }

    public static function forComment(User $reporter, Comment $comment, FeedReportReason $reason, ?string $details): self
    {
        $report = new self($reporter, $reason, $details);
        $report->comment = $comment;

        return $report;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReporter(): User
    {
        return $this->reporter;
    }

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function getComment(): ?Comment
    {
        return $this->comment;
    }

    public function getReason(): FeedReportReason
    {
        return $this->reason;
    }

    public function getDetails(): ?string
    {
        return $this->details;
    }

    public function getStatus(): FeedReportStatus
    {
        return $this->status;
    }

    public function getDecision(): ?FeedReportDecision
    {
        return $this->decision;
    }

    public function getAdminNote(): ?string
    {
        return $this->adminNote;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getResolvedAt(): ?DateTimeImmutable
    {
        return $this->resolvedAt;
    }

    public function getResolvedBy(): ?User
    {
        return $this->resolvedBy;
    }

    public function resolve(User $admin, FeedReportDecision $decision, ?string $adminNote): void
    {
        if ($this->status === FeedReportStatus::Resolved) {
            return;
        }

        $this->status = FeedReportStatus::Resolved;
        $this->decision = $decision;
        $this->adminNote = $adminNote;
        $this->resolvedAt = new DateTimeImmutable();
        $this->resolvedBy = $admin;
    }
}
