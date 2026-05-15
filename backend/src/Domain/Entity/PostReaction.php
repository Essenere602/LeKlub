<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\ValueObject\PostReactionType;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'post_reaction')]
#[ORM\UniqueConstraint(name: 'uniq_post_reaction_post_user', columns: ['post_id', 'user_id'])]
#[ORM\Index(name: 'idx_post_reaction_post_type', columns: ['post_id', 'type'])]
class PostReaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Post::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Post $post;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(length: 10, enumType: PostReactionType::class)]
    private PostReactionType $type;

    #[ORM\Column]
    private DateTimeImmutable $createdAt;

    #[ORM\Column]
    private DateTimeImmutable $updatedAt;

    public function __construct(Post $post, User $user, PostReactionType $type)
    {
        $now = new DateTimeImmutable();

        $this->post = $post;
        $this->user = $user;
        $this->type = $type;
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    public function getType(): PostReactionType
    {
        return $this->type;
    }

    public function changeType(PostReactionType $type): void
    {
        if ($this->type === $type) {
            return;
        }

        $this->type = $type;
        $this->updatedAt = new DateTimeImmutable();
    }
}
