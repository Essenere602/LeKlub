<?php

declare(strict_types=1);

namespace App\Tests\Application\User;

use App\Application\User\AvatarStorageInterface;
use App\Application\User\GetCurrentUserProfileUseCase;
use App\Application\User\UploadCurrentUserAvatarUseCase;
use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class UploadCurrentUserAvatarUseCaseTest extends TestCase
{
    public function testItUploadsCurrentUserAvatar(): void
    {
        $user = new User('user@example.com', 'samuel', 'hash');
        $repository = new UploadAvatarUserRepository();
        $storage = new UploadAvatarStorage();
        $useCase = new UploadCurrentUserAvatarUseCase(
            $repository,
            $storage,
            new GetCurrentUserProfileUseCase(),
        );

        $file = new UploadedFile(__FILE__, 'avatar.png', 'image/png', null, true);
        $result = $useCase->execute($user, $file, 'http://localhost:8080');

        self::assertSame($user, $repository->savedUser);
        self::assertSame($file, $storage->storedFile);
        self::assertSame('http://localhost:8080', $storage->publicBaseUrl);
        self::assertSame('http://localhost:8080/uploads/avatars/avatar.png', $user->getProfile()?->getAvatarUrl());
        self::assertSame('http://localhost:8080/uploads/avatars/avatar.png', $result['profile']['avatarUrl']);
    }
}

final class UploadAvatarStorage implements AvatarStorageInterface
{
    public ?UploadedFile $storedFile = null;
    public ?string $publicBaseUrl = null;

    public function store(UploadedFile $file, string $publicBaseUrl, ?string $previousAvatarUrl): string
    {
        $this->storedFile = $file;
        $this->publicBaseUrl = $publicBaseUrl;

        return $publicBaseUrl.'/uploads/avatars/avatar.png';
    }
}

final class UploadAvatarUserRepository implements UserRepositoryInterface
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
