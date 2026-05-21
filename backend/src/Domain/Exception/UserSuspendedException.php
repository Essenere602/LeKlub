<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use DomainException;

final class UserSuspendedException extends DomainException
{
    public static function forWriteAction(): self
    {
        return new self('Account temporarily suspended.');
    }
}
