<?php

declare(strict_types=1);

namespace App\Controller\Api\Auth;

use App\Application\Auth\RegisterUserUseCase;
use App\Domain\Exception\DuplicateUserException;
use App\DTO\Auth\RegisterRequest;
use App\Shared\Api\ApiResponse;
use JsonException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class RegisterController
{
    public function __construct(
        private readonly RegisterUserUseCase $registerUser,
        private readonly ValidatorInterface $validator,
    ) {
    }

    #[Route('/api/auth/register', name: 'api_auth_register', methods: ['POST'])]
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

        $registerRequest = RegisterRequest::fromArray($payload);
        $violations = $this->validator->validate($registerRequest);

        if (count($violations) > 0) {
            return ApiResponse::validationError($violations);
        }

        try {
            $user = $this->registerUser->execute($registerRequest);
        } catch (DuplicateUserException $exception) {
            return ApiResponse::error('User already exists.', [
                'user' => [$exception->getMessage()],
            ], 409);
        }

        return ApiResponse::success([
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'username' => $user->getUsername(),
                'roles' => $user->getRoles(),
            ],
        ], 'User registered successfully.', 201);
    }
}
