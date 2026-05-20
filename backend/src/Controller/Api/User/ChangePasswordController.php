<?php

declare(strict_types=1);

namespace App\Controller\Api\User;

use App\Application\User\ChangeCurrentUserPasswordUseCase;
use App\Domain\Entity\User;
use App\DTO\User\ChangePasswordRequest;
use App\Shared\Api\ApiResponse;
use DomainException;
use JsonException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ChangePasswordController
{
    public function __construct(
        private readonly Security $security,
        private readonly ChangeCurrentUserPasswordUseCase $changeCurrentUserPassword,
        private readonly ValidatorInterface $validator,
    ) {
    }

    #[Route('/api/me/password', name: 'api_me_password_update', methods: ['PATCH'])]
    public function __invoke(Request $request): JsonResponse
    {
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            return ApiResponse::error('Authentication required.', [], 401);
        }

        try {
            $payload = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return ApiResponse::error('Invalid JSON payload.', [], 400);
        }

        if (!is_array($payload)) {
            return ApiResponse::error('Invalid JSON payload.', [], 400);
        }

        $changePasswordRequest = ChangePasswordRequest::fromArray($payload);
        $violations = $this->validator->validate($changePasswordRequest);

        if (count($violations) > 0) {
            return ApiResponse::validationError($violations);
        }

        try {
            $this->changeCurrentUserPassword->execute($user, $changePasswordRequest);
        } catch (DomainException) {
            return ApiResponse::error('Unable to update password.', [], 400);
        }

        return ApiResponse::success([], 'Password updated successfully.');
    }
}
