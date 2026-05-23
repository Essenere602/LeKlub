<?php

declare(strict_types=1);

namespace App\Controller\Api\User;

use App\Application\User\UpdateCurrentUserAccountUseCase;
use App\Domain\Entity\User;
use App\Domain\Exception\DuplicateUserException;
use App\DTO\User\UpdateAccountRequest;
use App\Shared\Api\ApiResponse;
use DomainException;
use JsonException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class UpdateAccountController
{
    public function __construct(
        private readonly Security $security,
        private readonly UpdateCurrentUserAccountUseCase $updateCurrentUserAccount,
        private readonly ValidatorInterface $validator,
    ) {
    }

    #[Route('/api/me/account', name: 'api_me_account_update', methods: ['PATCH'])]
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

        $updateRequest = UpdateAccountRequest::fromArray($payload);
        $violations = $this->validator->validate($updateRequest);

        if (count($violations) > 0) {
            return ApiResponse::validationError($violations);
        }

        try {
            $updatedUser = $this->updateCurrentUserAccount->execute($user, $updateRequest);
        } catch (DuplicateUserException $exception) {
            return ApiResponse::error($this->duplicateMessage($exception), [], 409);
        } catch (DomainException) {
            return ApiResponse::error('Unable to update account.', [], 400);
        }

        return ApiResponse::success([
            'user' => $updatedUser,
        ], 'Account updated successfully.');
    }

    private function duplicateMessage(DuplicateUserException $exception): string
    {
        return match ($exception->getMessage()) {
            'EMAIL_ALREADY_USED' => 'Email already used.',
            'USERNAME_ALREADY_USED' => 'Username already used.',
            default => 'Account identifier already used.',
        };
    }
}
