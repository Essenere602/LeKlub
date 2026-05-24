<?php

declare(strict_types=1);

namespace App\Shared\Api;

use App\Domain\Entity\User;
use JsonException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

trait ApiControllerHelpers
{
    protected function currentUser(Security $security): User
    {
        $user = $security->getUser();

        if (!$user instanceof User) {
            throw new \LogicException('Authenticated user expected.');
        }

        return $user;
    }

    /**
     * @return array<string, mixed>|JsonResponse
     */
    protected function jsonPayload(Request $request): array|JsonResponse
    {
        try {
            $payload = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return ApiResponse::error('Invalid JSON payload.', [], 400);
        }

        return is_array($payload) ? $payload : ApiResponse::error('Invalid JSON payload.', [], 400);
    }

    protected function validationError(object $dto, ValidatorInterface $validator): ?JsonResponse
    {
        $violations = $validator->validate($dto);

        if (count($violations) === 0) {
            return null;
        }

        return ApiResponse::validationError($violations);
    }
}
