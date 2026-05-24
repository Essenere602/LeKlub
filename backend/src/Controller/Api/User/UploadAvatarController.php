<?php

declare(strict_types=1);

namespace App\Controller\Api\User;

use App\Application\User\UploadCurrentUserAvatarUseCase;
use App\Domain\Entity\User;
use App\Shared\Api\ApiResponse;
use DomainException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class UploadAvatarController
{
    public function __construct(
        private readonly Security $security,
        private readonly UploadCurrentUserAvatarUseCase $uploadCurrentUserAvatar,
    ) {
    }

    #[Route('/api/me/avatar', name: 'api_me_avatar_upload', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            return ApiResponse::error('Authentication required.', [], 401);
        }

        $avatar = $request->files->get('avatar');

        if (!$avatar instanceof UploadedFile) {
            return ApiResponse::error('Avatar image is required.', ['avatar' => ['Avatar image is required.']], 422);
        }

        try {
            return ApiResponse::success([
                'user' => $this->uploadCurrentUserAvatar->execute($user, $avatar, $request->getSchemeAndHttpHost()),
            ], 'Avatar updated successfully.');
        } catch (DomainException $exception) {
            return ApiResponse::error($this->messageFor($exception->getMessage()), [
                'avatar' => [$this->messageFor($exception->getMessage())],
            ], 422);
        }
    }

    private function messageFor(string $code): string
    {
        return match ($code) {
            'AVATAR_UPLOAD_TOO_LARGE' => 'Avatar image must be 2 MB or less.',
            'AVATAR_UPLOAD_UNSUPPORTED_TYPE' => 'Avatar image must be a JPG, PNG or WEBP file.',
            default => 'Avatar image is invalid.',
        };
    }
}
