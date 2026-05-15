<?php

declare(strict_types=1);

namespace App\Controller\Api\Feed;

use App\Application\Feed\ReactToPostUseCase;
use App\Application\Feed\RemovePostReactionUseCase;
use App\Domain\Entity\User;
use App\Domain\Exception\ResourceNotFoundException;
use App\DTO\Feed\ReactToPostRequest;
use App\Shared\Api\ApiResponse;
use JsonException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/feed')]
final class ReactionController
{
    public function __construct(
        private readonly Security $security,
        private readonly ValidatorInterface $validator,
    ) {
    }

    #[Route('/{postId}/reaction', name: 'api_feed_reaction_set', methods: ['PUT'], requirements: ['postId' => '\d+'])]
    public function react(int $postId, Request $request, ReactToPostUseCase $useCase): JsonResponse
    {
        $payload = $this->jsonPayload($request);

        if ($payload instanceof JsonResponse) {
            return $payload;
        }

        $dto = ReactToPostRequest::fromArray($payload);
        $violations = $this->validator->validate($dto);

        if (count($violations) > 0) {
            return ApiResponse::validationError($violations);
        }

        try {
            return ApiResponse::success([
                'post' => $useCase->execute($postId, $this->currentUser(), $dto),
            ], 'Reaction saved successfully.');
        } catch (ResourceNotFoundException) {
            return ApiResponse::error('Post not found.', [], 404);
        }
    }

    #[Route('/{postId}/reaction', name: 'api_feed_reaction_remove', methods: ['DELETE'], requirements: ['postId' => '\d+'])]
    public function remove(int $postId, RemovePostReactionUseCase $useCase): JsonResponse
    {
        try {
            return ApiResponse::success([
                'post' => $useCase->execute($postId, $this->currentUser()),
            ], 'Reaction removed successfully.');
        } catch (ResourceNotFoundException) {
            return ApiResponse::error('Post not found.', [], 404);
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
