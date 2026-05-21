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

    public static function alreadyResolved(): self
    {
        return new self('Report already resolved.');
    }

    public static function contentUnavailable(): self
    {
        return new self('Reported content is no longer available.');
    }
}
