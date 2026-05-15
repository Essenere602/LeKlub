<?php

declare(strict_types=1);

namespace App\Application\Football;

use App\Infrastructure\Football\FootballDataClientInterface;

final class ListFootballScorersUseCase
{
    public function __construct(
        private readonly FootballDataClientInterface $footballData,
    ) {
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function execute(string $competitionCode, int $limit): array
    {
        $competitionCode = FootballQuery::normalizeCompetitionCode($competitionCode);

        return $this->footballData->getScorers($competitionCode, FootballQuery::normalizeLimit($limit));
    }
}
