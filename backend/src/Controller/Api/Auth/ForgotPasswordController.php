<?php

declare(strict_types=1);

namespace App\Controller\Api\Auth;

use App\Application\Auth\RequestPasswordResetUseCase;
use App\DTO\Auth\ForgotPasswordRequest;
use App\Shared\Api\ApiResponse;
use JsonException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ForgotPasswordController
{
    public function __construct(
        private readonly RequestPasswordResetUseCase $requestPasswordReset,
        private readonly ValidatorInterface $validator,
    ) {
    }

    #[Route('/api/auth/forgot-password', name: 'api_auth_forgot_password', methods: ['POST'])]
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

        $forgotPasswordRequest = ForgotPasswordRequest::fromArray($payload);
        $violations = $this->validator->validate($forgotPasswordRequest);

        if (count($violations) > 0) {
            return ApiResponse::validationError($violations);
        }

        $this->requestPasswordReset->execute($forgotPasswordRequest);

        return ApiResponse::success([], 'If this email exists, reset instructions have been sent.');
    }
}
