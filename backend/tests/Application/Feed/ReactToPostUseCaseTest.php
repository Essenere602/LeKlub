<?php

declare(strict_types=1);

namespace App\Tests\Application\Feed;

use App\Application\Feed\FeedPresenter;
use App\Application\Feed\ReactToPostUseCase;
use App\Application\Feed\RemovePostReactionUseCase;
use App\Domain\Entity\Post;
use App\Domain\Entity\PostReaction;
use App\Domain\Entity\User;
use App\Domain\Repository\PostReactionRepositoryInterface;
use App\Domain\Repository\PostRepositoryInterface;
use App\Domain\ValueObject\PostReactionType;
use App\DTO\Feed\ReactToPostRequest;
use PHPUnit\Framework\TestCase;

final class ReactToPostUseCaseTest extends TestCase
{
    public function testUserCanLikeDislikeAndRemoveReaction(): void
    {
        $post = new Post(new User('author@example.com', 'author', 'hash'), 'Hello');
        $user = new User('user@example.com', 'user', 'hash');
        $posts = new VisiblePostRepository($post);
        $reactions = new InMemoryPostReactionRepository();
        $presenter = new FeedPresenter($posts);
        $react = new ReactToPostUseCase($posts, $reactions, $presenter);
        $remove = new RemovePostReactionUseCase($posts, $reactions, $presenter);

        $likedPost = $react->execute(1, $user, ReactToPostRequest::fromArray(['type' => 'like']));

        self::assertSame(1, $likedPost['likesCount']);
        self::assertSame(0, $likedPost['dislikesCount']);

        $dislikedPost = $react->execute(1, $user, ReactToPostRequest::fromArray(['type' => 'dislike']));

        self::assertSame(0, $dislikedPost['likesCount']);
        self::assertSame(1, $dislikedPost['dislikesCount']);
        self::assertCount(1, $reactions->reactions);

        $withoutReaction = $remove->execute(1, $user);

        self::assertSame(0, $withoutReaction['likesCount']);
        self::assertSame(0, $withoutReaction['dislikesCount']);
        self::assertCount(0, $reactions->reactions);
    }
}

final class VisiblePostRepository implements PostRepositoryInterface
{
    public function __construct(private Post $post)
    {
    }

    public function save(Post $post): void
    {
    }

    public function findVisibleById(int $id): ?Post
    {
        return $this->post->isDeleted() ? null : $this->post;
    }

    public function paginateVisible(int $page, int $limit): array
    {
        return [$this->post];
    }

    public function countVisible(): int
    {
        return 1;
    }

    public function paginateVisibleForAdmin(int $page, int $limit): array
    {
        return [$this->post];
    }

    public function countVisibleComments(Post $post): int
    {
        return 0;
    }

    public function countReactions(Post $post, PostReactionType $type): int
    {
        return count(array_filter(
            InMemoryPostReactionRepository::$sharedReactions,
            fn (PostReaction $reaction): bool => $reaction->getType() === $type
        ));
    }
}

final class InMemoryPostReactionRepository implements PostReactionRepositoryInterface
{
    /**
     * @var list<PostReaction>
     */
    public static array $sharedReactions = [];

    /**
     * @var list<PostReaction>
     */
    public array $reactions = [];

    public function __construct()
    {
        self::$sharedReactions = [];
    }

    public function save(PostReaction $reaction): void
    {
        if ($this->reactions === []) {
            $this->reactions[] = $reaction;
        }

        self::$sharedReactions = $this->reactions;
    }

    public function remove(PostReaction $reaction): void
    {
        $this->reactions = array_values(array_filter(
            $this->reactions,
            fn (PostReaction $saved): bool => $saved !== $reaction
        ));
        self::$sharedReactions = $this->reactions;
    }

    public function findForUserAndPost(User $user, Post $post): ?PostReaction
    {
        return $this->reactions[0] ?? null;
    }
}
