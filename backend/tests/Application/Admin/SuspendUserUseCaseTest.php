<?php

declare(strict_types=1);

namespace App\Tests\Application\Admin;

use App\Application\Admin\AdminPresenter;
use App\Application\Admin\SuspendUserUseCase;
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
use App\DTO\Admin\SuspendUserRequest;
use PHPUnit\Framework\TestCase;

final class SuspendUserUseCaseTest extends TestCase
{
    public function testItSuspendsUserAndSendsNotification(): void
    {
        $admin = new User('admin@example.com', 'admin', 'hash');
        $admin->setRoles(['ROLE_ADMIN']);
        $user = new User('user@example.com', 'user', 'hash');
        $users = new SuspensionUserRepository($user);
        $notifications = new SuspensionNotificationRepository();
        $useCase = new SuspendUserUseCase($users, $notifications, new AdminPresenter(new SuspensionPostRepository(), new SuspensionWarningRepository()));

        $result = $useCase->execute(42, $admin, SuspendUserRequest::fromArray([
            'durationDays' => 7,
            'reason' => 'Comportement abusif',
        ]));

        self::assertTrue($user->isSuspended());
        self::assertSame($user, $users->savedUser);
        self::assertSame(SystemNotificationType::UserSuspended, $notifications->savedNotifications[0]->getType());
        self::assertSame('user', $result['username']);
        self::assertTrue($result['isSuspended']);
        self::assertNotNull($result['suspendedUntil']);
    }

    public function testItRejectsSelfSuspension(): void
    {
        $admin = new User('admin@example.com', 'admin', 'hash');
        $useCase = new SuspendUserUseCase(new SuspensionUserRepository($admin), new SuspensionNotificationRepository(), new AdminPresenter(new SuspensionPostRepository(), new SuspensionWarningRepository()));

        $this->expectException(AdminUserActionException::class);

        $useCase->execute(1, $admin, SuspendUserRequest::fromArray([
            'durationDays' => 7,
            'reason' => 'Invalid',
        ]));
    }

    public function testItRejectsAdminSuspension(): void
    {
        $admin = new User('admin@example.com', 'admin', 'hash');
        $target = new User('target@example.com', 'target', 'hash');
        $target->setRoles(['ROLE_ADMIN']);
        $useCase = new SuspendUserUseCase(new SuspensionUserRepository($target), new SuspensionNotificationRepository(), new AdminPresenter(new SuspensionPostRepository(), new SuspensionWarningRepository()));

        $this->expectException(AdminUserActionException::class);

        $useCase->execute(2, $admin, SuspendUserRequest::fromArray([
            'durationDays' => 7,
            'reason' => 'Invalid',
        ]));
    }

    public function testItRejectsUnknownUser(): void
    {
        $useCase = new SuspendUserUseCase(new SuspensionUserRepository(null), new SuspensionNotificationRepository(), new AdminPresenter(new SuspensionPostRepository(), new SuspensionWarningRepository()));

        $this->expectException(ResourceNotFoundException::class);

        $useCase->execute(404, new User('admin@example.com', 'admin', 'hash'), SuspendUserRequest::fromArray([
            'durationDays' => 7,
            'reason' => 'Invalid',
        ]));
    }
}

final class SuspensionUserRepository implements UserRepositoryInterface
{
    public ?User $savedUser = null;

    public function __construct(private readonly ?User $user)
    {
    }

    public function findOneByEmail(string $email): ?User
    {
        return null;
    }

    public function findOneByUsername(string $username): ?User
    {
        return null;
    }

    public function findById(int $id): ?User
    {
        return $this->user;
    }

    public function findForDirectory(User $currentUser, ?string $query, int $limit): array
    {
        return [];
    }

    public function paginateForAdmin(?string $query, int $page, int $limit): array
    {
        return [];
    }

    public function countForAdmin(?string $query): int
    {
        return 0;
    }

    public function save(User $user): void
    {
        $this->savedUser = $user;
    }
}

final class SuspensionNotificationRepository implements SystemNotificationRepositoryInterface
{
    /** @var list<SystemNotification> */
    public array $savedNotifications = [];

    public function save(SystemNotification $notification): void
    {
        $this->savedNotifications[] = $notification;
    }

    public function findForUser(int $id, User $user): ?SystemNotification
    {
        return null;
    }

    public function paginateForUser(User $user, int $page, int $limit): array
    {
        return [];
    }

    public function countForUser(User $user): int
    {
        return 0;
    }
}

final class SuspensionPostRepository implements PostRepositoryInterface
{
    public function save(Post $post): void {}
    public function findVisibleById(int $id): ?Post { return null; }
    public function paginateVisible(int $page, int $limit): array { return []; }
    public function countVisible(): int { return 0; }
    public function paginateVisibleForAdmin(int $page, int $limit): array { return []; }
    public function countVisibleComments(Post $post): int { return 0; }
    public function countReactions(Post $post, PostReactionType $type): int { return 0; }
}

final class SuspensionWarningRepository implements UserWarningRepositoryInterface
{
    public function save(UserWarning $warning): void {}
    public function countForUser(User $user): int { return 0; }
    public function paginateForAdmin(int $page, int $limit, ?int $userId, bool $suspendedOnly): array { return []; }
    public function countForAdmin(?int $userId, bool $suspendedOnly): int { return 0; }
}
