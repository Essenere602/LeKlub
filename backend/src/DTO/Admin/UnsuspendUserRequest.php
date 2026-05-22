<?php

declare(strict_types=1);

namespace App\DTO\Admin;

use Symfony\Component\Validator\Constraints as Assert;

final class UnsuspendUserRequest
{
    #[Assert\Length(max: 500)]
    public ?string $reason = null;

    public static function fromArray(array $data): self
    {
        $request = new self();
        $reason = is_string($data['reason'] ?? null) ? trim(strip_tags($data['reason'])) : null;
        $request->reason = $reason === '' ? null : $reason;

        return $request;
    }
}
