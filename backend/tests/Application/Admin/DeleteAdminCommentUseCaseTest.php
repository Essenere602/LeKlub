<?php

declare(strict_types=1);

namespace App\Tests\Application\Admin;

use App\Application\Admin\DeleteAdminCommentUseCase;
use App\Application\Feed\DeleteCommentUseCase;
use App\Domain\Entity\Comment;
use App\Domain\Entity\Post;
use App\Domain\Entity\User;
use App\Domain\Exception\ResourceNotFoundException;
use App\Domain\Repository\CommentRepositoryInterface;
use PHPUnit\Framework\TestCase;

final class DeleteAdminCommentUseCaseTest extends TestCase
{
    public function testItDeletesVisibleCommentLogicallyAsAdmin(): void
    {
        $author = new User('author@example.com', 'author', 'hash');
        $admin = new User('admin@example.com', 'admin', 'hash');
        $post = new Post($author, 'Post');
        $comment = new Comment($post, $author, 'Moderate comment');
        $repository = new AdminCommentRepository($comment);
        $useCase = new DeleteAdminCommentUseCase($repository, new DeleteCommentUseCase($repository));

        $useCase->execute(1, $admin);

        self::assertTrue($comment->isDeleted());
        self::assertSame($admin, $comment->getDeletedBy());
        self::assertSame($comment, $repository->savedComment);
    }

    public function testItFailsWhenCommentIsNotVisible(): void
    {
        $repository = new AdminCommentRepository(null);
        $useCase = new DeleteAdminCommentUseCase($repository, new DeleteCommentUseCase($repository));

        $this->expectException(ResourceNotFoundException::class);

        $useCase->execute(404, new User('admin@example.com', 'admin', 'hash'));
    }
}

final class AdminCommentRepository implements CommentRepositoryInterface
{
    public ?Comment $savedComment = null;

    public function __construct(private readonly ?Comment $comment)
    {
    }

    public function save(Comment $comment): void
    {
        $this->savedComment = $comment;
    }

    public function findVisibleById(int $id): ?Comment
    {
        return $this->comment;
    }

    public function paginateVisibleForPost(Post $post, int $page, int $limit): array
    {
        return $this->comment === null ? [] : [$this->comment];
    }

    public function countVisibleForPost(Post $post): int
    {
        return $this->comment === null ? 0 : 1;
    }

    public function paginateVisibleForAdmin(int $page, int $limit): array
    {
        return $this->comment === null ? [] : [$this->comment];
    }

    public function countVisible(): int
    {
        return $this->comment === null ? 0 : 1;
    }
}
