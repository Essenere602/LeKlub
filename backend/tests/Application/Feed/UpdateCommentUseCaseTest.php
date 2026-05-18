<?php

declare(strict_types=1);

namespace App\Tests\Application\Feed;

use App\Application\Feed\FeedPresenter;
use App\Application\Feed\UpdateCommentUseCase;
use App\Domain\Entity\Comment;
use App\Domain\Entity\Post;
use App\Domain\Entity\User;
use App\Domain\Exception\ResourceNotFoundException;
use App\Domain\Repository\CommentRepositoryInterface;
use App\Domain\Repository\PostRepositoryInterface;
use App\Domain\ValueObject\PostReactionType;
use App\DTO\Feed\UpdateCommentRequest;
use PHPUnit\Framework\TestCase;

final class UpdateCommentUseCaseTest extends TestCase
{
    public function testItUpdatesCommentContentWithNormalizedText(): void
    {
        $author = new User('author@example.com', 'author', 'hash');
        $comment = new Comment(new Post($author, 'Post'), $author, 'Old comment');
        $comments = new UpdatingCommentRepository();
        $useCase = new UpdateCommentUseCase($comments, new FeedPresenter(new EmptyPostRepositoryForCommentUpdate()));

        $result = $useCase->execute($comment, UpdateCommentRequest::fromArray([
            'content' => ' <em>Updated comment</em> ',
        ]));

        self::assertSame('Updated comment', $comment->getContent());
        self::assertSame('Updated comment', $result['content']);
        self::assertSame($comment, $comments->savedComment);
    }

    public function testItRefusesDeletedComment(): void
    {
        $author = new User('author@example.com', 'author', 'hash');
        $comment = new Comment(new Post($author, 'Post'), $author, 'Old comment');
        $comment->delete($author);
        $comments = new UpdatingCommentRepository();
        $useCase = new UpdateCommentUseCase($comments, new FeedPresenter(new EmptyPostRepositoryForCommentUpdate()));

        $this->expectException(ResourceNotFoundException::class);

        $useCase->execute($comment, UpdateCommentRequest::fromArray(['content' => 'Updated']));
    }
}

final class UpdatingCommentRepository implements CommentRepositoryInterface
{
    public ?Comment $savedComment = null;

    public function save(Comment $comment): void
    {
        $this->savedComment = $comment;
    }

    public function findVisibleById(int $id): ?Comment
    {
        return null;
    }

    public function paginateVisibleForPost(Post $post, int $page, int $limit): array
    {
        return [];
    }

    public function countVisibleForPost(Post $post): int
    {
        return 0;
    }

    public function paginateVisibleForAdmin(int $page, int $limit): array
    {
        return [];
    }

    public function countVisible(): int
    {
        return 0;
    }
}

final class EmptyPostRepositoryForCommentUpdate implements PostRepositoryInterface
{
    public function save(Post $post): void
    {
    }

    public function findVisibleById(int $id): ?Post
    {
        return null;
    }

    public function paginateVisible(int $page, int $limit): array
    {
        return [];
    }

    public function countVisible(): int
    {
        return 0;
    }

    public function paginateVisibleForAdmin(int $page, int $limit): array
    {
        return [];
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
