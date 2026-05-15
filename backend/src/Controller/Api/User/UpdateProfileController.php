<?php

declare(strict_types=1);

namespace App\Controller\Api\User;

use App\Application\User\UpdateCurrentUserProfileUseCase;
use App\Domain\Entity\User;
use App\DTO\User\UpdateProfileRequest;
use App\Shared\Api\ApiResponse;
use JsonException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class UpdateProfileController
{
    public function __construct(
        private readonly Security $security,
        private readonly UpdateCurrentUserProfileUseCase $updateCurrentUserProfile,
        private readonly ValidatorInterface $validator,
    ) {
    }

    #[Route('/api/me/profile', name: 'api_me_profile_update', methods: ['PATCH'])]
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

        $updateRequest = UpdateProfileRequest::fromArray($payload);
        $violations = $this->validator->validate($updateRequest);

        if (count($violations) > 0) {
            return ApiResponse::validationError($violations);
        }

        return ApiResponse::success([
            'user' => $this->updateCurrentUserProfile->execute($user, $updateRequest),
        ], 'Profile updated successfully.');
    }
}
