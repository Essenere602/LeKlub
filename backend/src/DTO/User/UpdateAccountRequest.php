<?php

declare(strict_types=1);

namespace App\DTO\User;

use Symfony\Component\Validator\Constraints as Assert;

final class UpdateAccountRequest
{
    #[Assert\NotBlank(allowNull: true)]
    #[Assert\Email]
    #[Assert\Length(max: 180)]
    public ?string $email = null;

    #[Assert\NotBlank(allowNull: true)]
    #[Assert\Length(min: 3, max: 50)]
    #[Assert\Regex(pattern: '/^[a-zA-Z0-9_]+$/', message: 'Username can only contain letters, numbers and underscores.')]
    public ?string $username = null;

    #[Assert\Length(max: 4096)]
    public ?string $currentPassword = null;

    public static function fromArray(array $data): self
    {
        $request = new self();
        $request->email = array_key_exists('email', $data) ? self::lowerTrim($data['email']) : null;
        $request->username = array_key_exists('username', $data) ? self::trimString($data['username']) : null;
        $request->currentPassword = self::nullableString($data['currentPassword'] ?? null);

        return $request;
    }

    private static function lowerTrim(mixed $value): string
    {
        return mb_strtolower(self::trimString($value));
    }

    private static function trimString(mixed $value): string
    {
        return trim((string) $value);
    }

    private static function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = (string) $value;

        return $value === '' ? null : $value;
    }
}
