<?php

declare(strict_types=1);

namespace App\Controller\Api\Admin;

use App\Application\Admin\ListFeedReportsUseCase;
use App\Application\Admin\ResolveFeedReportUseCase;
use App\Domain\Entity\User;
use App\Domain\Exception\ResourceNotFoundException;
use App\Shared\Api\ApiResponse;
use App\Shared\Api\Pagination;
use InvalidArgumentException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/admin/reports')]
#[IsGranted('ROLE_ADMIN')]
final class AdminReportController
{
    public function __construct(
        private readonly Security $security,
    ) {
    }

    #[Route('', name: 'api_admin_reports_list', methods: ['GET'])]
    public function list(Request $request, ListFeedReportsUseCase $useCase): JsonResponse
    {
        try {
            return ApiResponse::success($useCase->execute(
                Pagination::fromRequest($request),
                $request->query->get('status')
            ));
        } catch (InvalidArgumentException) {
            return ApiResponse::error('Invalid report status.', [], 422);
        }
    }

    #[Route('/{id}/resolve', name: 'api_admin_reports_resolve', methods: ['PATCH'], requirements: ['id' => '\d+'])]
    public function resolve(int $id, ResolveFeedReportUseCase $useCase): JsonResponse
    {
        try {
            return ApiResponse::success([
                'report' => $useCase->execute($id, $this->currentUser()),
            ], 'Report resolved successfully.');
        } catch (ResourceNotFoundException) {
            return ApiResponse::error('Report not found.', [], 404);
        }
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
