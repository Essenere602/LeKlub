<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'feed_comment')]
#[ORM\Index(name: 'idx_comment_post_deleted', columns: ['post_id', 'deleted_at'])]
#[ORM\Index(name: 'idx_comment_created_at', columns: ['created_at'])]
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Post::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Post $post;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $author;

    #[ORM\Column(type: 'text')]
    private string $content;

    #[ORM\Column]
    private DateTimeImmutable $createdAt;

    #[ORM\Column]
    private DateTimeImmutable $updatedAt;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $deletedAt = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $deletedBy = null;

    public function __construct(Post $post, User $author, string $content)
    {
        $now = new DateTimeImmutable();

        $this->post = $post;
        $this->author = $author;
        $this->content = $content;
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPost(): Post
    {
        return $this->post;
    }

    public function getAuthor(): User
    {
        return $this->author;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getDeletedAt(): ?DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function getDeletedBy(): ?User
    {
        return $this->deletedBy;
    }

    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }

    public function updateContent(string $content): void
    {
        if ($this->deletedAt !== null) {
            return;
        }

        $this->content = $content;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function delete(User $deletedBy): void
    {
        if ($this->deletedAt !== null) {
            return;
        }

        $this->deletedAt = new DateTimeImmutable();
        $this->deletedBy = $deletedBy;
        $this->updatedAt = new DateTimeImmutable();
    }
}
