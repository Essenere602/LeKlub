<?php

declare(strict_types=1);

namespace App\Controller\Api\Auth;

use App\Application\Auth\ResetPasswordUseCase;
use App\DTO\Auth\ResetPasswordRequest;
use App\Shared\Api\ApiResponse;
use DomainException;
use JsonException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ResetPasswordController
{
    public function __construct(
        private readonly ResetPasswordUseCase $resetPassword,
        private readonly ValidatorInterface $validator,
        #[Autowire(service: 'limiter.reset_password')]
        private readonly RateLimiterFactory $resetPasswordLimiter,
    ) {
    }

    #[Route('/api/auth/reset-password', name: 'api_auth_reset_password', methods: ['POST'])]
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

        $rateLimit = $this->resetPasswordLimiter
            ->create($request->getClientIp() ?? 'unknown')
            ->consume();

        if (!$rateLimit->isAccepted()) {
            return ApiResponse::error('Too many attempts. Please try again later.', [], 429);
        }

        $resetPasswordRequest = ResetPasswordRequest::fromArray($payload);
        $violations = $this->validator->validate($resetPasswordRequest);

        if (count($violations) > 0) {
            return ApiResponse::validationError($violations);
        }

        try {
            $this->resetPassword->execute($resetPasswordRequest);
        } catch (DomainException) {
            return ApiResponse::error('Unable to reset password.', [], 400);
        }

        return ApiResponse::success([], 'Password reset successfully.');
    }
}
