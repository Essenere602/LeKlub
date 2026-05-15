<?php

declare(strict_types=1);

namespace App\Controller\Api\Messaging;

use App\Application\Messaging\CreateOrGetConversationUseCase;
use App\Application\Messaging\ListConversationsUseCase;
use App\Domain\Entity\User;
use App\Domain\Exception\MessagingException;
use App\Domain\Exception\ResourceNotFoundException;
use App\DTO\Messaging\CreateConversationRequest;
use App\Shared\Api\ApiResponse;
use JsonException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/conversations')]
final class ConversationController
{
    public function __construct(
        private readonly Security $security,
        private readonly ValidatorInterface $validator,
    ) {
    }

    #[Route('', name: 'api_conversations_list', methods: ['GET'])]
    public function list(ListConversationsUseCase $useCase): JsonResponse
    {
        return ApiResponse::success([
            'conversations' => $useCase->execute($this->currentUser()),
        ]);
    }

    #[Route('', name: 'api_conversations_create', methods: ['POST'])]
    public function create(Request $request, CreateOrGetConversationUseCase $useCase): JsonResponse
    {
        $payload = $this->jsonPayload($request);

        if ($payload instanceof JsonResponse) {
            return $payload;
        }

        $dto = CreateConversationRequest::fromArray($payload);
        $violations = $this->validator->validate($dto);

        if (count($violations) > 0) {
            return ApiResponse::validationError($violations);
        }

        try {
            return ApiResponse::success([
                'conversation' => $useCase->execute($this->currentUser(), $dto),
            ], 'Conversation ready.', 201);
        } catch (MessagingException) {
            return ApiResponse::error('Cannot create a conversation with yourself.', [], 400);
        } catch (ResourceNotFoundException) {
            return ApiResponse::error('Recipient not found.', [], 404);
        }
    }

    private function currentUser(): User
    {
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            throw new \LogicException('Authenticated user expected.');
        }

        return $user;
    }

    /**
     * @return array<string, mixed>|JsonResponse
     */
    private function jsonPayload(Request $request): array|JsonResponse
    {
        try {
            $payload = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return ApiResponse::error('Invalid JSON payload.', [], 400);
        }

        return is_array($payload) ? $payload : ApiResponse::error('Invalid JSON payload.', [], 400);
    }
}
