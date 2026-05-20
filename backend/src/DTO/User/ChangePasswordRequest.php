<?php

declare(strict_types=1);

namespace App\DTO\User;

use Symfony\Component\Validator\Constraints as Assert;

final class ChangePasswordRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $currentPassword = '';

    #[Assert\NotBlank]
    #[Assert\Length(min: 10, max: 128)]
    #[Assert\Regex(
        pattern: '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
        message: 'Password must contain at least one lowercase letter, one uppercase letter and one number.'
    )]
    public string $newPassword = '';

    #[Assert\NotBlank]
    #[Assert\EqualTo(propertyPath: 'newPassword', message: 'Password confirmation does not match.')]
    public string $newPasswordConfirmation = '';

    public static function fromArray(array $data): self
    {
        $request = new self();
        $request->currentPassword = (string) ($data['currentPassword'] ?? '');
        $request->newPassword = (string) ($data['newPassword'] ?? '');
        $request->newPasswordConfirmation = (string) ($data['newPasswordConfirmation'] ?? '');

        return $request;
    }
}
