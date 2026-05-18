<?php

declare(strict_types=1);

namespace App\Tests\Application\Feed;

use App\Application\Feed\FeedPresenter;
use App\Application\Feed\UpdatePostUseCase;
use App\Domain\Entity\Post;
use App\Domain\Entity\User;
use App\Domain\Exception\ResourceNotFoundException;
use App\Domain\Repository\PostRepositoryInterface;
use App\Domain\ValueObject\PostReactionType;
use App\DTO\Feed\UpdatePostRequest;
use PHPUnit\Framework\TestCase;

final class UpdatePostUseCaseTest extends TestCase
{
    public function testItUpdatesPostContentWithNormalizedText(): void
    {
        $post = new Post(new User('author@example.com', 'author', 'hash'), 'Old content');
        $repository = new UpdatingPostRepository();
        $useCase = new UpdatePostUseCase($repository, new FeedPresenter($repository));

        $result = $useCase->execute($post, UpdatePostRequest::fromArray([
            'content' => ' <strong>Updated content</strong> ',
        ]));

        self::assertSame('Updated content', $post->getContent());
        self::assertSame('Updated content', $result['content']);
        self::assertSame($post, $repository->savedPost);
    }

    public function testItRefusesDeletedPost(): void
    {
        $author = new User('author@example.com', 'author', 'hash');
        $post = new Post($author, 'Old content');
        $post->delete($author);
        $repository = new UpdatingPostRepository();
        $useCase = new UpdatePostUseCase($repository, new FeedPresenter($repository));

        $this->expectException(ResourceNotFoundException::class);

        $useCase->execute($post, UpdatePostRequest::fromArray(['content' => 'Updated']));
    }
}

final class UpdatingPostRepository implements PostRepositoryInterface
{
    public ?Post $savedPost = null;

    public function save(Post $post): void
    {
        $this->savedPost = $post;
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
