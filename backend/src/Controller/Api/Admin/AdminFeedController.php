<?php

declare(strict_types=1);

namespace App\Controller\Api\Admin;

use App\Application\Admin\DeleteAdminCommentUseCase;
use App\Application\Admin\DeleteAdminPostUseCase;
use App\Application\Admin\ListAdminCommentsUseCase;
use App\Application\Admin\ListAdminPostsUseCase;
use App\Domain\Entity\User;
use App\Domain\Exception\ResourceNotFoundException;
use App\Shared\Api\ApiResponse;
use App\Shared\Api\Pagination;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/admin')]
#[IsGranted('ROLE_ADMIN')]
final class AdminFeedController
{
    public function __construct(
        private readonly Security $security,
    ) {
    }

    #[Route('/posts', name: 'api_admin_posts_list', methods: ['GET'])]
    public function listPosts(Request $request, ListAdminPostsUseCase $useCase): JsonResponse
    {
        return ApiResponse::success($useCase->execute(Pagination::fromRequest($request)));
    }

    #[Route('/posts/{id}', name: 'api_admin_posts_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function deletePost(int $id, DeleteAdminPostUseCase $useCase): JsonResponse
    {
        try {
            $useCase->execute($id, $this->currentUser());

            return ApiResponse::success([], 'Post deleted successfully.');
        } catch (ResourceNotFoundException) {
            return ApiResponse::error('Post not found.', [], 404);
        }
    }

    #[Route('/comments', name: 'api_admin_comments_list', methods: ['GET'])]
    public function listComments(Request $request, ListAdminCommentsUseCase $useCase): JsonResponse
    {
        return ApiResponse::success($useCase->execute(Pagination::fromRequest($request)));
    }

    #[Route('/comments/{id}', name: 'api_admin_comments_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function deleteComment(int $id, DeleteAdminCommentUseCase $useCase): JsonResponse
    {
        try {
            $useCase->execute($id, $this->currentUser());

            return ApiResponse::success([], 'Comment deleted successfully.');
        } catch (ResourceNotFoundException) {
            return ApiResponse::error('Comment not found.', [], 404);
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
}
