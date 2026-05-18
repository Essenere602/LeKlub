<?php

declare(strict_types=1);

namespace App\Tests\Application\Admin;

use App\Application\Admin\DeleteAdminPostUseCase;
use App\Application\Feed\DeletePostUseCase;
use App\Domain\Entity\Post;
use App\Domain\Entity\User;
use App\Domain\Exception\ResourceNotFoundException;
use App\Domain\Repository\PostRepositoryInterface;
use App\Domain\ValueObject\PostReactionType;
use PHPUnit\Framework\TestCase;

final class DeleteAdminPostUseCaseTest extends TestCase
{
    public function testItDeletesVisiblePostLogicallyAsAdmin(): void
    {
        $author = new User('author@example.com', 'author', 'hash');
        $admin = new User('admin@example.com', 'admin', 'hash');
        $post = new Post($author, 'Moderate me');
        $repository = new AdminPostRepository($post);
        $useCase = new DeleteAdminPostUseCase($repository, new DeletePostUseCase($repository));

        $useCase->execute(1, $admin);

        self::assertTrue($post->isDeleted());
        self::assertSame($admin, $post->getDeletedBy());
        self::assertSame($post, $repository->savedPost);
    }

    public function testItFailsWhenPostIsNotVisible(): void
    {
        $repository = new AdminPostRepository(null);
        $useCase = new DeleteAdminPostUseCase($repository, new DeletePostUseCase($repository));

        $this->expectException(ResourceNotFoundException::class);

        $useCase->execute(404, new User('admin@example.com', 'admin', 'hash'));
    }
}

final class AdminPostRepository implements PostRepositoryInterface
{
    public ?Post $savedPost = null;

    public function __construct(private readonly ?Post $post)
    {
    }

    public function save(Post $post): void
    {
        $this->savedPost = $post;
    }

    public function findVisibleById(int $id): ?Post
    {
        return $this->post;
    }

    public function paginateVisible(int $page, int $limit): array
    {
        return $this->post === null ? [] : [$this->post];
    }

    public function countVisible(): int
    {
        return $this->post === null ? 0 : 1;
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
