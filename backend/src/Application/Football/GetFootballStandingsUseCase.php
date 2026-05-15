<?php

declare(strict_types=1);

namespace App\Application\Football;

use App\Infrastructure\Football\FootballDataClientInterface;

final class GetFootballStandingsUseCase
{
    public function __construct(
        private readonly FootballDataClientInterface $footballData,
    ) {
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function execute(string $competitionCode): array
    {
        $competitionCode = FootballQuery::normalizeCompetitionCode($competitionCode);

        return $this->footballData->getStandings($competitionCode);
    }
}
