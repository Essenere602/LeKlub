<?php

declare(strict_types=1);

namespace App\Application\User;

use App\Domain\Entity\User;

final class GetCurrentUserProfileUseCase
{
    /**
     * @return array<string, mixed>
     */
    public function execute(User $user): array
    {
        $profile = $user->getProfile();

        return [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'username' => $user->getUsername(),
            'roles' => $user->getRoles(),
            'profile' => [
                'displayName' => $profile?->getDisplayName(),
                'bio' => $profile?->getBio(),
                'favoriteTeamName' => $profile?->getFavoriteTeamName(),
                'avatarUrl' => $profile?->getAvatarUrl(),
            ],
            'createdAt' => $user->getCreatedAt()->format(DATE_ATOM),
        ];
    }
}
