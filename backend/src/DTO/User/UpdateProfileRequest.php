<?php

declare(strict_types=1);

namespace App\DTO\User;

use Symfony\Component\Validator\Constraints as Assert;

final class UpdateProfileRequest
{
    /**
     * @var array<string, bool>
     */
    private array $providedFields = [];

    #[Assert\Length(max: 80)]
    public ?string $displayName = null;

    #[Assert\Length(max: 500)]
    public ?string $bio = null;

    #[Assert\Length(max: 100)]
    public ?string $favoriteTeamName = null;

    #[Assert\Url]
    #[Assert\Length(max: 255)]
    public ?string $avatarUrl = null;

    public static function fromArray(array $data): self
    {
        $request = new self();
        $request->providedFields = array_fill_keys(array_keys($data), true);
        $request->displayName = self::nullableTrim($data['displayName'] ?? null);
        $request->bio = self::nullableTrim($data['bio'] ?? null);
        $request->favoriteTeamName = self::nullableTrim($data['favoriteTeamName'] ?? null);
        $request->avatarUrl = self::nullableTrim($data['avatarUrl'] ?? null);

        return $request;
    }

    public function has(string $field): bool
    {
        return isset($this->providedFields[$field]);
    }

    private static function nullableTrim(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
