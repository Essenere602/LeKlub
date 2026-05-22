<?php

declare(strict_types=1);

namespace App\DTO\Admin;

use Symfony\Component\Validator\Constraints as Assert;

final class SuspendUserRequest
{
    private const ALLOWED_DURATIONS = [1, 7, 30];

    #[Assert\NotBlank]
    #[Assert\Choice(choices: self::ALLOWED_DURATIONS)]
    public int $durationDays = 7;

    #[Assert\NotBlank]
    #[Assert\Length(max: 500)]
    public string $reason = '';

    public static function fromArray(array $data): self
    {
        $request = new self();
        $request->durationDays = is_numeric($data['durationDays'] ?? null) ? (int) $data['durationDays'] : 0;
        $request->reason = is_string($data['reason'] ?? null) ? trim(strip_tags($data['reason'])) : '';

        return $request;
    }

    /**
     * @return list<int>
     */
    public static function allowedDurations(): array
    {
        return self::ALLOWED_DURATIONS;
    }
}
