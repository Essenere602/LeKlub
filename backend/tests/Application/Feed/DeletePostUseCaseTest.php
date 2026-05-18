<?php

declare(strict_types=1);

namespace App\Tests\Application\Feed;

use App\Application\Feed\DeletePostUseCase;
use App\Domain\Entity\Post;
use App\Domain\Entity\User;
use App\Domain\Repository\PostRepositoryInterface;
use App\Domain\ValueObject\PostReactionType;
use PHPUnit\Framework\TestCase;

final class DeletePostUseCaseTest extends TestCase
{
    public function testItDeletesPostLogically(): void
    {
        $author = new User('author@example.com', 'author', 'hash');
        $admin = new User('admin@example.com', 'admin', 'hash');
        $post = new Post($author, 'Hello');
        $repository = new SavingPostRepository([$post]);
        $useCase = new DeletePostUseCase($repository);

        $useCase->execute($post, $admin);

        self::assertTrue($post->isDeleted());
        self::assertSame($admin, $post->getDeletedBy());
        self::assertSame($post, $repository->savedPost);
        self::assertSame([], $repository->paginateVisible(1, 10));
    }
}

final class SavingPostRepository implements PostRepositoryInterface
{
    public ?Post $savedPost = null;

    /**
     * @param list<Post> $posts
     */
    public function __construct(private array $posts = [])
    {
    }

    public function save(Post $post): void
    {
        $this->savedPost = $post;
    }

    public function findVisibleById(int $id): ?Post
    {
        foreach ($this->posts as $post) {
            if (!$post->isDeleted()) {
                return $post;
            }
        }

        return null;
    }

    public function paginateVisible(int $page, int $limit): array
    {
        return array_values(array_filter($this->posts, fn (Post $post): bool => !$post->isDeleted()));
    }

    public function countVisible(): int
    {
        return count($this->paginateVisible(1, 10));
    }

    public function paginateVisibleForAdmin(int $page, int $limit): array
    {
        return $this->paginateVisible($page, $limit);
    }

    public function countVisibleComments(Post $post): int
    {
        return 0;
    }

    public function countReactions(Post $post, PostReactionType $type): int
    {
        return 0;
    }
}
