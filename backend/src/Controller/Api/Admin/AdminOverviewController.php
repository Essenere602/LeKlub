<?php

declare(strict_types=1);

namespace App\Controller\Api\Admin;

use App\Application\Admin\GetAdminOverviewUseCase;
use App\Shared\Api\ApiResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/admin')]
#[IsGranted('ROLE_ADMIN')]
final class AdminOverviewController
{
    #[Route('/overview', name: 'api_admin_overview', methods: ['GET'])]
    public function overview(GetAdminOverviewUseCase $useCase): JsonResponse
    {
        return ApiResponse::success($useCase->execute());
    }
}
