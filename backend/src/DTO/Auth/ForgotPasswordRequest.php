<?php

declare(strict_types=1);

namespace App\DTO\Auth;

use Symfony\Component\Validator\Constraints as Assert;

final class ForgotPasswordRequest
{
    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 180)]
    public string $email = '';

    public static function fromArray(array $data): self
    {
        $request = new self();
        $request->email = mb_strtolower(trim((string) ($data['email'] ?? '')));

        return $request;
    }
}
