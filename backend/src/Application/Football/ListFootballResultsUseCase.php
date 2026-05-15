<?php

declare(strict_types=1);

namespace App\Application\Football;

use App\Infrastructure\Football\FootballDataClientInterface;

final class ListFootballResultsUseCase
{
    public function __construct(
        private readonly FootballDataClientInterface $footballData,
    ) {
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function execute(string $competitionCode, int $limit, ?int $matchday = null): array
    {
        $competitionCode = FootballQuery::normalizeCompetitionCode($competitionCode);

        return $this->footballData->getResults(
            $competitionCode,
            FootballQuery::normalizeLimit($limit),
            $matchday === null ? null : FootballQuery::normalizeMatchday($matchday),
        );
    }
}
