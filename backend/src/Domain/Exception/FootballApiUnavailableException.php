<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use RuntimeException;

final class FootballApiUnavailableException extends RuntimeException
{
    public static function unavailable(): self
    {
        return new self('FOOTBALL_API_UNAVAILABLE');
    }
}
