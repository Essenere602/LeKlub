<?php

declare(strict_types=1);

namespace App\Tests\Application\Admin;

use App\Application\Admin\AdminPresenter;
use App\Application\Admin\DemoteAdminUserUseCase;
use App\Domain\Entity\Post;
use App\Domain\Entity\SystemNotification;
use App\Domain\Entity\User;
use App\Domain\Entity\UserWarning;
use App\Domain\Exception\AdminUserActionException;
use App\Domain\Exception\ResourceNotFoundException;
use App\Domain\Repository\AdminRoleUserRepositoryInterface;
use App\Domain\Repository\PostRepositoryInterface;
use App\Domain\Repository\SystemNotificationRepositoryInterface;
use App\Domain\Repository\UserWarningRepositoryInterface;
use App\Domain\ValueObject\PostReactionType;
use App\Domain\ValueObject\SystemNotificationType;
use PHPUnit\Framework\TestCase;

final class DemoteAdminUserUseCaseTest extends TestCase
{
    public function testItDemotesAdminAndSendsNotification(): void
    {
        $admin = self::user(1, 'admin@example.com', 'admin', ['ROLE_ADMIN']);
        $target = self::user(2, 'target@example.com', 'target', ['ROLE_ADMIN']);
        $users = new DemotionUserRepository($target, 2);
        $notifications = new DemotionNotificationRepository();
        $useCase = new DemoteAdminUserUseCase($users, $notifications, self::presenter());

        $result = $useCase->execute(2, $admin);

        self::assertFalse($target->isAdmin());
        self::assertSame(['ROLE_USER'], $target->getRoles());
        self::assertSame($target, $users->savedUser);
        self::assertSame(SystemNotificationType::AdminRoleRemoved, $notifications->savedNotifications[0]->getType());
        self::assertSame(['ROLE_USER'], $result['roles']);
    }

    public function testItRejectsSelfRoleChange(): void
    {
        $admin = self::user(1, 'admin@example.com', 'admin', ['ROLE_ADMIN']);
        $useCase = new DemoteAdminUserUseCase(new DemotionUserRepository($admin, 2), new DemotionNotificationRepository(), self::presenter());

        $this->expectException(AdminUserActionException::class);

        $useCase->execute(1, $admin);
    }

    public function testItRejectsLastAdminDemotion(): void
    {
        $admin = self::user(1, 'admin@example.com', 'admin', ['ROLE_ADMIN']);
        $target = self::user(2, 'target@example.com', 'target', ['ROLE_ADMIN']);
        $useCase = new DemoteAdminUserUseCase(new DemotionUserRepository($target, 1), new DemotionNotificationRepository(), self::presenter());

        $this->expectException(AdminUserActionException::class);

        $useCase->execute(2, $admin);
    }

    public function testItRejectsUnknownUser(): void
    {
        $useCase = new DemoteAdminUserUseCase(new DemotionUserRepository(null, 2), new DemotionNotificationRepository(), self::presenter());

        $this->expectException(ResourceNotFoundException::class);

        $useCase->execute(404, self::user(1, 'admin@example.com', 'admin', ['ROLE_ADMIN']));
    }

    /**
     * @param list<string> $roles
     */
    private static function user(int $id, string $email, string $username, array $roles): User
    {
        $user = new User($email, $username, 'hash');
        $user->setRoles($roles);
        $property = new \ReflectionProperty(User::class, 'id');
        $property->setValue($user, $id);

        return $user;
    }

    private static function presenter(): AdminPresenter
    {
        return new AdminPresenter(new DemotionPostRepository(), new DemotionWarningRepository());
    }
}

final class DemotionUserRepository implements AdminRoleUserRepositoryInterface
{
    public ?User $savedUser = null;

    public function __construct(private readonly ?User $user, private readonly int $adminsCount)
    {
    }

    public function findById(int $id): ?User
    {
        return $this->user;
    }

    public function countAdmins(): int
    {
        return $this->adminsCount;
    }

    public function save(User $user): void
    {
        $this->savedUser = $user;
    }
}

final class DemotionNotificationRepository implements SystemNotificationRepositoryInterface
{
    /** @var list<SystemNotification> */
    public array $savedNotifications = [];

    public function save(SystemNotification $notification): void { $this->savedNotifications[] = $notification; }
    public function findForUser(int $id, User $user): ?SystemNotification { return null; }
    public function paginateForUser(User $user, int $page, int $limit): array { return []; }
    public function countForUser(User $user): int { return 0; }
}

final class DemotionPostRepository implements PostRepositoryInterface
{
    public function save(Post $post): void {}
    public function findVisibleById(int $id): ?Post { return null; }
    public function paginateVisible(int $page, int $limit): array { return []; }
    public function countVisible(): int { return 0; }
    public function paginateVisibleForAdmin(int $page, int $limit): array { return []; }
    public function countVisibleComments(Post $post): int { return 0; }
    public function countReactions(Post $post, PostReactionType $type): int { return 0; }
}

final class DemotionWarningRepository implements UserWarningRepositoryInterface
{
    public function save(UserWarning $warning): void {}
    public function countForUser(User $user): int { return 0; }
    public function paginateForAdmin(int $page, int $limit, ?int $userId, bool $suspendedOnly): array { return []; }
    public function countForAdmin(?int $userId, bool $suspendedOnly): int { return 0; }
}
