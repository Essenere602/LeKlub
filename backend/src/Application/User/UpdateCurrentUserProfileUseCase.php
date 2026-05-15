<?php

declare(strict_types=1);

namespace App\Application\User;

use App\Domain\Entity\User;
use App\Domain\Entity\UserProfile;
use App\Domain\Repository\UserRepositoryInterface;
use App\DTO\User\UpdateProfileRequest;

final class UpdateCurrentUserProfileUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly GetCurrentUserProfileUseCase $getCurrentUserProfile,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(User $user, UpdateProfileRequest $request): array
    {
        $profile = $user->getProfile() ?? new UserProfile($user);

        $profile->update(
            $request->has('displayName') ? $request->displayName : $profile->getDisplayName(),
            $request->has('bio') ? $request->bio : $profile->getBio(),
            $request->has('favoriteTeamName') ? $request->favoriteTeamName : $profile->getFavoriteTeamName(),
            $request->has('avatarUrl') ? $request->avatarUrl : $profile->getAvatarUrl(),
        );

        $this->users->save($user);

        return $this->getCurrentUserProfile->execute($user);
    }
}
