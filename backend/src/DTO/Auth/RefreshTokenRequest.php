<?php

declare(strict_types=1);

namespace App\DTO\Auth;

use Symfony\Component\Validator\Constraints as Assert;

final class RefreshTokenRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 32, max: 255)]
    public string $refreshToken = '';

    public static function fromArray(array $data): self
    {
        $request = new self();
        $request->refreshToken = trim((string) ($data['refreshToken'] ?? ''));

        return $request;
    }
}
