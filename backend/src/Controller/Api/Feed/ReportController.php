<?php

declare(strict_types=1);

namespace App\Controller\Api\Feed;

use App\Application\Feed\ReportCommentUseCase;
use App\Application\Feed\ReportPostUseCase;
use App\Domain\Entity\User;
use App\Domain\Exception\FeedReportException;
use App\Domain\Exception\ResourceNotFoundException;
use App\DTO\Feed\CreateFeedReportRequest;
use App\Shared\Api\ApiResponse;
use JsonException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/feed')]
final class ReportController
{
    public function __construct(
        private readonly Security $security,
        private readonly ValidatorInterface $validator,
    ) {
    }

    #[Route('/{postId}/reports', name: 'api_feed_posts_report', methods: ['POST'], requirements: ['postId' => '\d+'])]
    public function reportPost(int $postId, Request $request, ReportPostUseCase $useCase): JsonResponse
    {
        $payload = $this->jsonPayload($request);

        if ($payload instanceof JsonResponse) {
            return $payload;
        }

        $dto = CreateFeedReportRequest::fromArray($payload);
        $violations = $this->validator->validate($dto);

        if (count($violations) > 0) {
            return ApiResponse::validationError($violations);
        }

        try {
            $report = $useCase->execute($postId, $this->currentUser(), $dto);

            return ApiResponse::success([
                'report' => [
                    'id' => $report->getId(),
                    'status' => $report->getStatus()->value,
                ],
            ], 'Report created successfully.', 201);
        } catch (ResourceNotFoundException) {
            return ApiResponse::error('Post not found.', [], 404);
        } catch (FeedReportException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 409);
        }
    }

    #[Route('/comments/{commentId}/reports', name: 'api_feed_comments_report', methods: ['POST'], requirements: ['commentId' => '\d+'])]
    public function reportComment(int $commentId, Request $request, ReportCommentUseCase $useCase): JsonResponse
    {
        $payload = $this->jsonPayload($request);

        if ($payload instanceof JsonResponse) {
            return $payload;
        }

        $dto = CreateFeedReportRequest::fromArray($payload);
        $violations = $this->validator->validate($dto);

        if (count($violations) > 0) {
            return ApiResponse::validationError($violations);
        }

        try {
            $report = $useCase->execute($commentId, $this->currentUser(), $dto);

            return ApiResponse::success([
                'report' => [
                    'id' => $report->getId(),
                    'status' => $report->getStatus()->value,
                ],
            ], 'Report created successfully.', 201);
        } catch (ResourceNotFoundException) {
            return ApiResponse::error('Comment not found.', [], 404);
        } catch (FeedReportException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 409);
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
