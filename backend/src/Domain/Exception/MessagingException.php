<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use DomainException;

final class MessagingException extends DomainException
{
    public static function cannotMessageSelf(): self
    {
        return new self('CANNOT_MESSAGE_SELF');
    }
}
