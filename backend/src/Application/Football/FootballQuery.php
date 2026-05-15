<?php

declare(strict_types=1);

namespace App\Application\Football;

use App\Domain\Exception\UnsupportedFootballCompetitionException;
use App\Domain\ValueObject\FootballCompetition;

final class FootballQuery
{
    public static function normalizeCompetitionCode(string $code): string
    {
        $code = strtoupper(trim($code));

        if (!FootballCompetition::isSupported($code)) {
            throw UnsupportedFootballCompetitionException::forCode($code);
        }

        return $code;
    }

    public static function normalizeLimit(int $limit, int $default = 10, int $max = 20): int
    {
        if ($limit <= 0) {
            return $default;
        }

        return min($limit, $max);
    }

    public static function normalizeMatchday(int $matchday, int $max = 38): ?int
    {
        if ($matchday <= 0) {
            return null;
        }

        return min($matchday, $max);
    }
}
