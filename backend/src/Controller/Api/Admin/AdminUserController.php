<?php

declare(strict_types=1);

namespace App\Controller\Api\Admin;

use App\Application\Admin\ListAdminUsersUseCase;
use App\Shared\Api\ApiResponse;
use App\Shared\Api\Pagination;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/admin/users')]
#[IsGranted('ROLE_ADMIN')]
final class AdminUserController
{
    #[Route('', name: 'api_admin_users_list', methods: ['GET'])]
    public function list(Request $request, ListAdminUsersUseCase $useCase): JsonResponse
    {
        return ApiResponse::success($useCase->execute(
            Pagination::fromRequest($request),
            $request->query->getString('query'),
        ));
    }
}
