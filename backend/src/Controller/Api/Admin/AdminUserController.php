<?php

declare(strict_types=1);

namespace App\Controller\Api\Admin;

use App\Application\Admin\ListAdminUsersUseCase;
use App\Application\Admin\SuspendUserUseCase;
use App\Application\Admin\UnsuspendUserUseCase;
use App\Domain\Entity\User;
use App\Domain\Exception\AdminUserActionException;
use App\Domain\Exception\ResourceNotFoundException;
use App\DTO\Admin\SuspendUserRequest;
use App\DTO\Admin\UnsuspendUserRequest;
use App\Shared\Api\ApiResponse;
use App\Shared\Api\Pagination;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/admin/users')]
#[IsGranted('ROLE_ADMIN')]
final class AdminUserController
{
    public function __construct(
        private readonly Security $security,
        private readonly ValidatorInterface $validator,
    ) {
    }

    #[Route('', name: 'api_admin_users_list', methods: ['GET'])]
    public function list(Request $request, ListAdminUsersUseCase $useCase): JsonResponse
    {
        return ApiResponse::success($useCase->execute(
            Pagination::fromRequest($request),
            $request->query->getString('query'),
        ));
    }

    #[Route('/{id}/suspend', name: 'api_admin_users_suspend', methods: ['PATCH'], requirements: ['id' => '\d+'])]
    public function suspend(int $id, Request $request, SuspendUserUseCase $useCase): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);
        $dto = SuspendUserRequest::fromArray(is_array($payload) ? $payload : []);
        $errors = $this->validator->validate($dto);

        if (count($errors) > 0) {
            return ApiResponse::validationError($errors);
        }

        try {
            return ApiResponse::success(['user' => $useCase->execute($id, $this->currentUser(), $dto)], 'User suspended successfully.');
        } catch (ResourceNotFoundException) {
            return ApiResponse::error('User not found.', [], 404);
        } catch (AdminUserActionException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 403);
        }
    }

    #[Route('/{id}/unsuspend', name: 'api_admin_users_unsuspend', methods: ['PATCH'], requirements: ['id' => '\d+'])]
    public function unsuspend(int $id, Request $request, UnsuspendUserUseCase $useCase): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);
        $dto = UnsuspendUserRequest::fromArray(is_array($payload) ? $payload : []);
        $errors = $this->validator->validate($dto);

        if (count($errors) > 0) {
            return ApiResponse::validationError($errors);
        }

        try {
            return ApiResponse::success(['user' => $useCase->execute($id, $this->currentUser(), $dto)], 'User unsuspended successfully.');
        } catch (ResourceNotFoundException) {
            return ApiResponse::error('User not found.', [], 404);
        } catch (AdminUserActionException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 403);
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
