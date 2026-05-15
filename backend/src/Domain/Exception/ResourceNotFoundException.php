<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use DomainException;

final class ResourceNotFoundException extends DomainException
{
    public static function post(): self
    {
        return new self('POST_NOT_FOUND');
    }

    public static function comment(): self
    {
        return new self('COMMENT_NOT_FOUND');
    }

    public static function user(): self
    {
        return new self('USER_NOT_FOUND');
    }

    public static function conversation(): self
    {
        return new self('CONVERSATION_NOT_FOUND');
    }
}
