<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use DomainException;

final class FeedReportException extends DomainException
{
    public static function alreadyReported(): self
    {
        return new self('Content already reported by this user.');
    }
}
