<?php

declare(strict_types=1);

namespace App\Tests\Application\User;

use App\Application\User\GetCurrentUserProfileUseCase;
use App\Application\User\UpdateCurrentUserProfileUseCase;
use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;
use App\DTO\User\UpdateProfileRequest;
use PHPUnit\Framework\TestCase;

final class UpdateCurrentUserProfileUseCaseTest extends TestCase
{
    public function testItUpdatesOnlyCurrentUserProfile(): void
    {
        $user = new User('user@example.com', 'samuel', 'hash');
        $repository = new SavingUserRepository();
        $useCase = new UpdateCurrentUserProfileUseCase($repository, new GetCurrentUserProfileUseCase());

        $result = $useCase->execute($user, UpdateProfileRequest::fromArray([
            'displayName' => 'Samuel',
            'bio' => 'Football fan',
            'favoriteTeamName' => 'Paris',
        ]));

        self::assertSame($user, $repository->savedUser);
        self::assertSame('Samuel', $user->getProfile()?->getDisplayName());
        self::assertSame('Football fan', $user->getProfile()?->getBio());
        self::assertSame('Paris', $user->getProfile()?->getFavoriteTeamName());
        self::assertSame('Samuel', $result['profile']['displayName']);
    }

    public function testItKeepsOmittedProfileFields(): void
    {
        $user = new User('user@example.com', 'samuel', 'hash');
        $repository = new SavingUserRepository();
        $useCase = new UpdateCurrentUserProfileUseCase($repository, new GetCurrentUserProfileUseCase());

        $useCase->execute($user, UpdateProfileRequest::fromArray([
            'displayName' => 'Samuel',
            'bio' => 'Initial bio',
        ]));

        $useCase->execute($user, UpdateProfileRequest::fromArray([
            'displayName' => 'Sam',
        ]));

        self::assertSame('Sam', $user->getProfile()?->getDisplayName());
        self::assertSame('Initial bio', $user->getProfile()?->getBio());
    }
}

final class SavingUserRepository implements UserRepositoryInterface
{
    public ?User $savedUser = null;

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
        return null;
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
