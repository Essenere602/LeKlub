<?php

declare(strict_types=1);

namespace App\Controller\Api\User;

use App\Application\User\ListUsersUseCase;
use App\Domain\Entity\User;
use App\Shared\Api\ApiResponse;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/users')]
final class UserDirectoryController
{
    public function __construct(
        private readonly Security $security,
    ) {
    }

    #[Route('', name: 'api_users_directory', methods: ['GET'])]
    public function list(Request $request, ListUsersUseCase $useCase): JsonResponse
    {
        return ApiResponse::success([
            'users' => $useCase->execute(
                $this->currentUser(),
                $request->query->getString('query'),
                $request->query->getInt('limit', 20),
            ),
        ]);
    }

    private function currentUser(): User
    {
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            throw new \LogicException('Authenticated user expected.');
        }

        return $user;
    }
}
