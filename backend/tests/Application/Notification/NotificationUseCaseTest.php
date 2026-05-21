<?php

declare(strict_types=1);

namespace App\Tests\Application\Notification;

use App\Application\Notification\ListCurrentUserNotificationsUseCase;
use App\Application\Notification\MarkNotificationAsReadUseCase;
use App\Application\Notification\NotificationPresenter;
use App\Domain\Entity\SystemNotification;
use App\Domain\Entity\User;
use App\Domain\Exception\ResourceNotFoundException;
use App\Domain\Repository\SystemNotificationRepositoryInterface;
use App\Domain\ValueObject\SystemNotificationType;
use App\Shared\Api\Pagination;
use PHPUnit\Framework\TestCase;

final class NotificationUseCaseTest extends TestCase
{
    public function testItListsCurrentUserNotifications(): void
    {
        $user = new User('user@example.com', 'user', 'hash');
        $repository = new InMemoryNotificationRepository([
            new SystemNotification($user, SystemNotificationType::Warning, 'Warning', 'Message'),
        ]);
        $useCase = new ListCurrentUserNotificationsUseCase($repository, new NotificationPresenter());

        $result = $useCase->execute($user, new Pagination(1, 10));

        self::assertCount(1, $result['notifications']);
        self::assertSame(1, $result['pagination']['total']);
    }

    public function testItMarksOwnNotificationAsRead(): void
    {
        $user = new User('user@example.com', 'user', 'hash');
        $notification = new SystemNotification($user, SystemNotificationType::Warning, 'Warning', 'Message');
        $repository = new InMemoryNotificationRepository([$notification]);
        $useCase = new MarkNotificationAsReadUseCase($repository, new NotificationPresenter());

        $result = $useCase->execute(1, $user);

        self::assertNotNull($notification->getReadAt());
        self::assertSame($notification, $repository->savedNotification);
        self::assertNotNull($result['readAt']);
    }

    public function testItRejectsUnknownNotification(): void
    {
        $useCase = new MarkNotificationAsReadUseCase(new InMemoryNotificationRepository(), new NotificationPresenter());

        $this->expectException(ResourceNotFoundException::class);

        $useCase->execute(404, new User('user@example.com', 'user', 'hash'));
    }
}

final class InMemoryNotificationRepository implements SystemNotificationRepositoryInterface
{
    public ?SystemNotification $savedNotification = null;

    /**
     * @param list<SystemNotification> $notifications
     */
    public function __construct(private readonly array $notifications = [])
    {
    }

    public function save(SystemNotification $notification): void
    {
        $this->savedNotification = $notification;
    }

    public function findForUser(int $id, User $user): ?SystemNotification
    {
        return $this->notifications[0] ?? null;
    }

    public function paginateForUser(User $user, int $page, int $limit): array
    {
        return $this->notifications;
    }

    public function countForUser(User $user): int
    {
        return count($this->notifications);
    }
}
