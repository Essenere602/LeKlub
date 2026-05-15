<?php

declare(strict_types=1);

namespace App\Application\User;

use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;

final class ListUsersUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function execute(User $currentUser, ?string $query, int $limit): array
    {
        $query = $this->normalizeQuery($query);
        $limit = $this->normalizeLimit($limit);

        return array_map(
            $this->presentUser(...),
            $this->users->findForDirectory($currentUser, $query, $limit)
        );
    }

    private function normalizeQuery(?string $query): ?string
    {
        $query = trim((string) $query);

        return $query === '' ? null : mb_substr($query, 0, 80);
    }

    private function normalizeLimit(int $limit): int
    {
        if ($limit <= 0) {
            return 20;
        }

        return min($limit, 20);
    }

    /**
     * @return array<string, mixed>
     */
    private function presentUser(User $user): array
    {
        $profile = $user->getProfile();

        return [
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'displayName' => $profile?->getDisplayName(),
            'avatarUrl' => $profile?->getAvatarUrl(),
        ];
    }
}
