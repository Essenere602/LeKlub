<?php

declare(strict_types=1);

namespace App\Application\User;

use App\Domain\Entity\User;
use App\Domain\Entity\UserProfile;
use App\Domain\Repository\UserRepositoryInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class UploadCurrentUserAvatarUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly AvatarStorageInterface $avatarStorage,
        private readonly GetCurrentUserProfileUseCase $getCurrentUserProfile,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(User $user, UploadedFile $avatar, string $publicBaseUrl): array
    {
        $profile = $user->getProfile() ?? new UserProfile($user);
        $avatarUrl = $this->avatarStorage->store($avatar, $publicBaseUrl, $profile->getAvatarUrl());

        $profile->updateAvatarUrl($avatarUrl);
        $this->users->save($user);

        return $this->getCurrentUserProfile->execute($user);
    }
}
