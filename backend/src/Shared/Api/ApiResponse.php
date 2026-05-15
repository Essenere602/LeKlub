<?php

declare(strict_types=1);

namespace App\Shared\Api;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\ConstraintViolationListInterface;

final class ApiResponse
{
    public static function success(array $data = [], ?string $message = null, int $status = 200): JsonResponse
    {
        return new JsonResponse([
            'success' => true,
            'data' => $data,
            'message' => $message,
            'errors' => [],
        ], $status);
    }

    public static function error(string $message, array $errors = [], int $status = 400): JsonResponse
    {
        return new JsonResponse([
            'success' => false,
            'data' => null,
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }

    public static function validationError(ConstraintViolationListInterface $violations): JsonResponse
    {
        $errors = [];

        foreach ($violations as $violation) {
            $errors[$violation->getPropertyPath()][] = $violation->getMessage();
        }

        return self::error('Validation failed.', $errors, 422);
    }
}
