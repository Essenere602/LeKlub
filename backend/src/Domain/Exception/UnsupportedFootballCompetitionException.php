<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use DomainException;

final class UnsupportedFootballCompetitionException extends DomainException
{
    public static function forCode(string $code): self
    {
        return new self(sprintf('UNSUPPORTED_FOOTBALL_COMPETITION_%s', strtoupper($code)));
    }
}
