<?php

declare(strict_types=1);

namespace App\Controller\Api\Admin;

use App\Application\Admin\ListUserWarningsUseCase;
use App\Shared\Api\ApiResponse;
use App\Shared\Api\Pagination;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/admin/warnings')]
#[IsGranted('ROLE_ADMIN')]
final class AdminWarningController
{
    #[Route('', name: 'api_admin_warnings_list', methods: ['GET'])]
    public function list(Request $request, ListUserWarningsUseCase $useCase): JsonResponse
    {
        $userId = $request->query->get('userId');

        return ApiResponse::success($useCase->execute(
            Pagination::fromRequest($request),
            is_numeric($userId) ? (int) $userId : null,
            $request->query->getBoolean('suspendedOnly')
        ));
    }
}
