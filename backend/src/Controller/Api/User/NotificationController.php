<?php

declare(strict_types=1);

namespace App\Controller\Api\User;

use App\Application\Notification\ListCurrentUserNotificationsUseCase;
use App\Application\Notification\MarkNotificationAsReadUseCase;
use App\Domain\Entity\User;
use App\Domain\Exception\ResourceNotFoundException;
use App\Shared\Api\ApiResponse;
use App\Shared\Api\Pagination;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/me/notifications')]
final class NotificationController
{
    public function __construct(
        private readonly Security $security,
    ) {
    }

    #[Route('', name: 'api_me_notifications_list', methods: ['GET'])]
    public function list(Request $request, ListCurrentUserNotificationsUseCase $useCase): JsonResponse
    {
        return ApiResponse::success($useCase->execute($this->currentUser(), Pagination::fromRequest($request)));
    }

    #[Route('/{id}/read', name: 'api_me_notifications_read', methods: ['PATCH'], requirements: ['id' => '\d+'])]
    public function read(int $id, MarkNotificationAsReadUseCase $useCase): JsonResponse
    {
        try {
            return ApiResponse::success([
                'notification' => $useCase->execute($id, $this->currentUser()),
            ], 'Notification marked as read.');
        } catch (ResourceNotFoundException) {
            return ApiResponse::error('Notification not found.', [], 404);
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
