<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

enum AdminContentStatus: string
{
    case Active = 'active';
    case Deleted = 'deleted';
    case All = 'all';

    public static function fromQuery(?string $status): self
    {
        return self::tryFrom($status ?? '') ?? self::Active;
    }
}
