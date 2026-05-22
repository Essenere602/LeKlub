<?php

declare(strict_types=1);

namespace App\Tests\Application\Admin;

use App\Application\Admin\AdminPresenter;
use App\Application\Admin\UnsuspendUserUseCase;
use App\Domain\Entity\Post;
use App\Domain\Entity\SystemNotification;
use App\Domain\Entity\User;
use App\Domain\Entity\UserWarning;
use App\Domain\Exception\AdminUserActionException;
use App\Domain\Exception\ResourceNotFoundException;
use App\Domain\Repository\PostRepositoryInterface;
use App\Domain\Repository\SystemNotificationRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\Repository\UserWarningRepositoryInterface;
use App\Domain\ValueObject\PostReactionType;
use App\Domain\ValueObject\SystemNotificationType;
use App\DTO\Admin\UnsuspendUserRequest;
use DateInterval;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class UnsuspendUserUseCaseTest extends TestCase
{
    public function testItUnsuspendsUserAndSendsNotification(): void
    {
        $admin = new User('admin@example.com', 'admin', 'hash');
        $user = new User('user@example.com', 'user', 'hash');
        $user->suspendUntil((new DateTimeImmutable())->add(new DateInterval('P7D')));
        $users = new UnsuspensionUserRepository($user);
        $notifications = new UnsuspensionNotificationRepository();
        $useCase = new UnsuspendUserUseCase($users, $notifications, new AdminPresenter(new UnsuspensionPostRepository(), new UnsuspensionWarningRepository()));

        $result = $useCase->execute(42, $admin, UnsuspendUserRequest::fromArray(['reason' => 'Vérification effectuée']));

        self::assertFalse($user->isSuspended());
        self::assertSame($user, $users->savedUser);
        self::assertSame(SystemNotificationType::UserUnsuspended, $notifications->savedNotifications[0]->getType());
        self::assertSame('user', $result['username']);
        self::assertFalse($result['isSuspended']);
        self::assertNull($result['suspendedUntil']);
    }

    public function testItIsIdempotentWhenUserIsNotSuspended(): void
    {
        $admin = new User('admin@example.com', 'admin', 'hash');
        $user = new User('user@example.com', 'user', 'hash');
        $users = new UnsuspensionUserRepository($user);
        $notifications = new UnsuspensionNotificationRepository();
        $useCase = new UnsuspendUserUseCase($users, $notifications, new AdminPresenter(new UnsuspensionPostRepository(), new UnsuspensionWarningRepository()));

        $useCase->execute(42, $admin, UnsuspendUserRequest::fromArray([]));

        self::assertSame($user, $users->savedUser);
        self::assertCount(1, $notifications->savedNotifications);
    }

    public function testItRejectsSelfUnsuspension(): void
    {
        $admin = new User('admin@example.com', 'admin', 'hash');
        $useCase = new UnsuspendUserUseCase(new UnsuspensionUserRepository($admin), new UnsuspensionNotificationRepository(), new AdminPresenter(new UnsuspensionPostRepository(), new UnsuspensionWarningRepository()));

        $this->expectException(AdminUserActionException::class);

        $useCase->execute(1, $admin, UnsuspendUserRequest::fromArray([]));
    }

    public function testItRejectsUnknownUser(): void
    {
        $useCase = new UnsuspendUserUseCase(new UnsuspensionUserRepository(null), new UnsuspensionNotificationRepository(), new AdminPresenter(new UnsuspensionPostRepository(), new UnsuspensionWarningRepository()));

        $this->expectException(ResourceNotFoundException::class);

        $useCase->execute(404, new User('admin@example.com', 'admin', 'hash'), UnsuspendUserRequest::fromArray([]));
    }
}

final class UnsuspensionUserRepository implements UserRepositoryInterface
{
    public ?User $savedUser = null;

    public function __construct(private readonly ?User $user)
    {
    }

    public function findOneByEmail(string $email): ?User { return null; }
    public function findOneByUsername(string $username): ?User { return null; }
    public function findById(int $id): ?User { return $this->user; }
    public function findForDirectory(User $currentUser, ?string $query, int $limit): array { return []; }
    public function paginateForAdmin(?string $query, int $page, int $limit): array { return []; }
    public function countForAdmin(?string $query): int { return 0; }
    public function save(User $user): void { $this->savedUser = $user; }
}

final class UnsuspensionNotificationRepository implements SystemNotificationRepositoryInterface
{
    /** @var list<SystemNotification> */
    public array $savedNotifications = [];

    public function save(SystemNotification $notification): void { $this->savedNotifications[] = $notification; }
    public function findForUser(int $id, User $user): ?SystemNotification { return null; }
    public function paginateForUser(User $user, int $page, int $limit): array { return []; }
    public function countForUser(User $user): int { return 0; }
}

final class UnsuspensionPostRepository implements PostRepositoryInterface
{
    public function save(Post $post): void {}
    public function findVisibleById(int $id): ?Post { return null; }
    public function paginateVisible(int $page, int $limit): array { return []; }
    public function countVisible(): int { return 0; }
    public function paginateVisibleForAdmin(int $page, int $limit): array { return []; }
    public function countVisibleComments(Post $post): int { return 0; }
    public function countReactions(Post $post, PostReactionType $type): int { return 0; }
}

final class UnsuspensionWarningRepository implements UserWarningRepositoryInterface
{
    public function save(UserWarning $warning): void {}
    public function countForUser(User $user): int { return 0; }
    public function paginateForAdmin(int $page, int $limit, ?int $userId, bool $suspendedOnly): array { return []; }
    public function countForAdmin(?int $userId, bool $suspendedOnly): int { return 0; }
}
