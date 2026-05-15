<?php

declare(strict_types=1);

namespace App\Controller\Api\Feed;

use App\Application\Feed\AddCommentUseCase;
use App\Application\Feed\DeleteCommentUseCase;
use App\Application\Feed\ListPostCommentsUseCase;
use App\Domain\Entity\User;
use App\Domain\Exception\ResourceNotFoundException;
use App\Domain\Repository\CommentRepositoryInterface;
use App\DTO\Feed\CreateCommentRequest;
use App\Security\Voter\CommentVoter;
use App\Shared\Api\ApiResponse;
use App\Shared\Api\Pagination;
use JsonException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/feed')]
final class CommentController
{
    public function __construct(
        private readonly Security $security,
        private readonly ValidatorInterface $validator,
        private readonly CommentRepositoryInterface $comments,
    ) {
    }

    #[Route('/{postId}/comments', name: 'api_feed_comments_list', methods: ['GET'], requirements: ['postId' => '\d+'])]
    public function list(int $postId, Request $request, ListPostCommentsUseCase $useCase): JsonResponse
    {
        try {
            return ApiResponse::success($useCase->execute($postId, Pagination::fromRequest($request)));
        } catch (ResourceNotFoundException) {
            return ApiResponse::error('Post not found.', [], 404);
        }
    }

    #[Route('/{postId}/comments', name: 'api_feed_comments_create', methods: ['POST'], requirements: ['postId' => '\d+'])]
    public function create(int $postId, Request $request, AddCommentUseCase $useCase): JsonResponse
    {
        $payload = $this->jsonPayload($request);

        if ($payload instanceof JsonResponse) {
            return $payload;
        }

        $dto = CreateCommentRequest::fromArray($payload);
        $violations = $this->validator->validate($dto);

        if (count($violations) > 0) {
            return ApiResponse::validationError($violations);
        }

        try {
            return ApiResponse::success([
                'comment' => $useCase->execute($postId, $this->currentUser(), $dto),
            ], 'Comment created successfully.', 201);
        } catch (ResourceNotFoundException) {
            return ApiResponse::error('Post not found.', [], 404);
        }
    }

    #[Route('/comments/{id}', name: 'api_feed_comments_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(int $id, DeleteCommentUseCase $useCase): JsonResponse
    {
        $comment = $this->comments->findVisibleById($id);

        if ($comment === null) {
            return ApiResponse::error('Comment not found.', [], 404);
        }

        if (!$this->security->isGranted(CommentVoter::DELETE, $comment)) {
            return ApiResponse::error('Access denied.', [], 403);
        }

        $useCase->execute($comment, $this->currentUser());

        return ApiResponse::success([], 'Comment deleted successfully.');
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
