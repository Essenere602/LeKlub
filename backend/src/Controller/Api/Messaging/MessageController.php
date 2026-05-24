<?php

declare(strict_types=1);

namespace App\Controller\Api\Messaging;

use App\Application\Messaging\HideMessageForCurrentUserUseCase;
use App\Application\Messaging\ListMessagesUseCase;
use App\Application\Messaging\MarkConversationAsReadUseCase;
use App\Application\Messaging\SendMessageUseCase;
use App\Domain\Exception\ResourceNotFoundException;
use App\Domain\Exception\UserSuspendedException;
use App\Domain\Repository\ConversationRepositoryInterface;
use App\DTO\Messaging\SendMessageRequest;
use App\Security\Voter\ConversationVoter;
use App\Shared\Api\ApiControllerHelpers;
use App\Shared\Api\ApiResponse;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/conversations')]
final class MessageController
{
    use ApiControllerHelpers;

    public function __construct(
        private readonly Security $security,
        private readonly ValidatorInterface $validator,
        private readonly ConversationRepositoryInterface $conversations,
    ) {
    }

    #[Route('/{id}/messages', name: 'api_conversation_messages_list', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function list(int $id, ListMessagesUseCase $useCase): JsonResponse
    {
        $conversation = $this->conversations->findById($id);

        if ($conversation === null) {
            return ApiResponse::error('Conversation not found.', [], 404);
        }

        if (!$this->security->isGranted(ConversationVoter::VIEW, $conversation)) {
            return ApiResponse::error('Access denied.', [], 403);
        }

        return ApiResponse::success([
            'messages' => $useCase->execute($conversation, $this->currentUser($this->security)),
        ]);
    }

    #[Route('/{id}/messages', name: 'api_conversation_messages_send', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function send(int $id, Request $request, SendMessageUseCase $useCase): JsonResponse
    {
        $conversation = $this->conversations->findById($id);

        if ($conversation === null) {
            return ApiResponse::error('Conversation not found.', [], 404);
        }

        if (!$this->security->isGranted(ConversationVoter::SEND_MESSAGE, $conversation)) {
            return ApiResponse::error('Access denied.', [], 403);
        }

        $payload = $this->jsonPayload($request);
        if ($payload instanceof JsonResponse) {
            return $payload;
        }

        $dto = SendMessageRequest::fromArray($payload);
        $validationError = $this->validationError($dto, $this->validator);

        if ($validationError !== null) {
            return $validationError;
        }

        try {
            return ApiResponse::success([
                'message' => $useCase->execute($conversation, $this->currentUser($this->security), $dto),
            ], 'Message sent.', 201);
        } catch (UserSuspendedException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 403);
        }
    }

    #[Route('/{id}/read', name: 'api_conversation_mark_read', methods: ['PATCH'], requirements: ['id' => '\d+'])]
    public function markAsRead(int $id, MarkConversationAsReadUseCase $useCase): JsonResponse
    {
        $conversation = $this->conversations->findById($id);

        if ($conversation === null) {
            return ApiResponse::error('Conversation not found.', [], 404);
        }

        if (!$this->security->isGranted(ConversationVoter::MARK_READ, $conversation)) {
            return ApiResponse::error('Access denied.', [], 403);
        }

        $useCase->execute($conversation, $this->currentUser($this->security));

        return ApiResponse::success([], 'Conversation marked as read.');
    }

    #[Route('/{id}/messages/{messageId}', name: 'api_conversation_message_hide', methods: ['DELETE'], requirements: ['id' => '\d+', 'messageId' => '\d+'])]
    public function hideForMe(int $id, int $messageId, HideMessageForCurrentUserUseCase $useCase): JsonResponse
    {
        $conversation = $this->conversations->findById($id);

        if ($conversation === null) {
            return ApiResponse::error('Conversation not found.', [], 404);
        }

        if (!$this->security->isGranted(ConversationVoter::VIEW, $conversation)) {
            return ApiResponse::error('Access denied.', [], 403);
        }

        try {
            $useCase->execute($conversation, $messageId, $this->currentUser($this->security));

            return ApiResponse::success([], 'Message hidden successfully.');
        } catch (ResourceNotFoundException) {
            return ApiResponse::error('Message not found.', [], 404);
        }
    }

}
