<?php

declare(strict_types=1);

namespace App\Controller\Api\Auth;

use App\Application\Auth\LogoutUseCase;
use App\DTO\Auth\LogoutRequest;
use App\Shared\Api\ApiResponse;
use JsonException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class LogoutController
{
    public function __construct(
        private readonly LogoutUseCase $logout,
        private readonly ValidatorInterface $validator,
    ) {
    }

    #[Route('/api/auth/logout', name: 'api_auth_logout', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $payload = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return ApiResponse::error('Invalid JSON payload.', [], 400);
        }

        if (!is_array($payload)) {
            return ApiResponse::error('Invalid JSON payload.', [], 400);
        }

        $logoutRequest = LogoutRequest::fromArray($payload);
        $violations = $this->validator->validate($logoutRequest);

        if (count($violations) > 0) {
            return ApiResponse::validationError($violations);
        }

        $this->logout->execute($logoutRequest);

        return ApiResponse::success([], 'Logged out successfully.');
    }
}
