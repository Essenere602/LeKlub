<?php

declare(strict_types=1);

namespace App\Controller\Api\Feed;

use App\Application\Feed\CreatePostUseCase;
use App\Application\Feed\DeletePostUseCase;
use App\Application\Feed\GetPostUseCase;
use App\Application\Feed\ListFeedUseCase;
use App\Application\Feed\UpdatePostUseCase;
use App\Domain\Entity\User;
use App\Domain\Exception\ResourceNotFoundException;
use App\Domain\Repository\PostRepositoryInterface;
use App\DTO\Feed\CreatePostRequest;
use App\DTO\Feed\UpdatePostRequest;
use App\Security\Voter\PostVoter;
use App\Shared\Api\ApiResponse;
use App\Shared\Api\Pagination;
use JsonException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/feed')]
final class FeedController
{
    public function __construct(
        private readonly Security $security,
        private readonly ValidatorInterface $validator,
        private readonly PostRepositoryInterface $posts,
    ) {
    }

    #[Route('', name: 'api_feed_list', methods: ['GET'])]
    public function list(Request $request, ListFeedUseCase $useCase): JsonResponse
    {
        return ApiResponse::success($useCase->execute(Pagination::fromRequest($request)));
    }

    #[Route('', name: 'api_feed_create', methods: ['POST'])]
    public function create(Request $request, CreatePostUseCase $useCase): JsonResponse
    {
        $user = $this->currentUser();
        $payload = $this->jsonPayload($request);

        if ($payload instanceof JsonResponse) {
            return $payload;
        }

        $dto = CreatePostRequest::fromArray($payload);
        $violations = $this->validator->validate($dto);

        if (count($violations) > 0) {
            return ApiResponse::validationError($violations);
        }

        return ApiResponse::success([
            'post' => $useCase->execute($user, $dto),
        ], 'Post created successfully.', 201);
    }

    #[Route('/{id}', name: 'api_feed_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id, GetPostUseCase $useCase): JsonResponse
    {
        try {
            return ApiResponse::success([
                'post' => $useCase->execute($id),
            ]);
        } catch (ResourceNotFoundException) {
            return ApiResponse::error('Post not found.', [], 404);
        }
    }

    #[Route('/{id}', name: 'api_feed_update', methods: ['PATCH'], requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request, UpdatePostUseCase $useCase): JsonResponse
    {
        $post = $this->posts->findVisibleById($id);

        if ($post === null) {
            return ApiResponse::error('Post not found.', [], 404);
        }

        if (!$this->security->isGranted(PostVoter::EDIT, $post)) {
            return ApiResponse::error('Access denied.', [], 403);
        }

        $payload = $this->jsonPayload($request);

        if ($payload instanceof JsonResponse) {
            return $payload;
        }

        $dto = UpdatePostRequest::fromArray($payload);
        $violations = $this->validator->validate($dto);

        if (count($violations) > 0) {
            return ApiResponse::validationError($violations);
        }

        try {
            return ApiResponse::success([
                'post' => $useCase->execute($post, $dto),
            ], 'Post updated successfully.');
        } catch (ResourceNotFoundException) {
            return ApiResponse::error('Post not found.', [], 404);
        }
    }

    #[Route('/{id}', name: 'api_feed_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(int $id, DeletePostUseCase $useCase): JsonResponse
    {
        $user = $this->currentUser();
        $post = $this->posts->findVisibleById($id);

        if ($post === null) {
            return ApiResponse::error('Post not found.', [], 404);
        }

        if (!$this->security->isGranted(PostVoter::DELETE, $post)) {
            return ApiResponse::error('Access denied.', [], 403);
        }

        $useCase->execute($post, $user);

        return ApiResponse::success([], 'Post deleted successfully.');
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
