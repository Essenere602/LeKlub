<?php

declare(strict_types=1);

namespace App\Controller\Api\User;

use App\Application\User\GetCurrentUserProfileUseCase;
use App\Domain\Entity\User;
use App\Shared\Api\ApiResponse;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class MeController
{
    public function __construct(
        private readonly Security $security,
        private readonly GetCurrentUserProfileUseCase $getCurrentUserProfile,
    ) {
    }

    #[Route('/api/me', name: 'api_me', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            return ApiResponse::error('Authentication required.', [], 401);
        }

        return ApiResponse::success([
            'user' => $this->getCurrentUserProfile->execute($user),
        ]);
    }
}
