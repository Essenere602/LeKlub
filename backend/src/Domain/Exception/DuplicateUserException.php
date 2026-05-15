<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use DomainException;

final class DuplicateUserException extends DomainException
{
    public static function email(): self
    {
        return new self('EMAIL_ALREADY_USED');
    }

    public static function username(): self
    {
        return new self('USERNAME_ALREADY_USED');
    }
}
