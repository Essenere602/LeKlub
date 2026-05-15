<?php

declare(strict_types=1);

namespace App\Infrastructure\Football;

interface FootballDataClientInterface
{
    /**
     * @return list<array<string, mixed>>
     */
    public function getResults(string $competitionCode, int $limit, ?int $matchday = null): array;

    /**
     * @return list<array<string, mixed>>
     */
    public function getUpcomingMatches(string $competitionCode, int $limit, ?int $matchday = null): array;

    /**
     * @return list<array<string, mixed>>
     */
    public function getStandings(string $competitionCode): array;

    /**
     * @return list<array<string, mixed>>
     */
    public function getScorers(string $competitionCode, int $limit): array;
}
