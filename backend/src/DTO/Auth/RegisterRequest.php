<?php

declare(strict_types=1);

namespace App\DTO\Auth;

use Symfony\Component\Validator\Constraints as Assert;

final class RegisterRequest
{
    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 180)]
    public string $email = '';

    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 50)]
    #[Assert\Regex(pattern: '/^[a-zA-Z0-9_]+$/', message: 'Username can only contain letters, numbers and underscores.')]
    public string $username = '';

    #[Assert\NotBlank]
    #[Assert\Length(min: 10, max: 128)]
    #[Assert\Regex(
        pattern: '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
        message: 'Password must contain at least one lowercase letter, one uppercase letter and one number.'
    )]
    public string $password = '';

    public static function fromArray(array $data): self
    {
        $request = new self();
        $request->email = trim((string) ($data['email'] ?? ''));
        $request->username = trim((string) ($data['username'] ?? ''));
        $request->password = (string) ($data['password'] ?? '');

        return $request;
    }
}
