<?php

declare(strict_types=1);

namespace App\Controller\Api\Auth;

use App\Application\Auth\RefreshAccessTokenUseCase;
use App\DTO\Auth\RefreshTokenRequest;
use App\Shared\Api\ApiResponse;
use DomainException;
use JsonException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class RefreshTokenController
{
    public function __construct(
        private readonly RefreshAccessTokenUseCase $refreshAccessToken,
        private readonly ValidatorInterface $validator,
        #[Autowire(service: 'limiter.refresh_token')]
        private readonly RateLimiterFactory $refreshTokenLimiter,
    ) {
    }

    #[Route('/api/auth/refresh', name: 'api_auth_refresh', methods: ['POST'])]
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

        $rateLimit = $this->refreshTokenLimiter
            ->create($request->getClientIp() ?? 'unknown')
            ->consume();

        if (!$rateLimit->isAccepted()) {
            return ApiResponse::error('Too many attempts. Please try again later.', [], 429);
        }

        $refreshTokenRequest = RefreshTokenRequest::fromArray($payload);
        $violations = $this->validator->validate($refreshTokenRequest);

        if (count($violations) > 0) {
            return ApiResponse::validationError($violations);
        }

        try {
            return new JsonResponse($this->refreshAccessToken->execute($refreshTokenRequest));
        } catch (DomainException) {
            return ApiResponse::error('Invalid refresh token.', [], 401);
        }
    }
}
