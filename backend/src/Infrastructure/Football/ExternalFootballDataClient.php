<?php

declare(strict_types=1);

namespace App\Infrastructure\Football;

use App\Domain\Exception\FootballApiUnavailableException;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class ExternalFootballDataClient implements FootballDataClientInterface
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $baseUrl,
        private readonly string $apiToken,
        private readonly float $timeout,
    ) {
    }

    public function getResults(string $competitionCode, int $limit, ?int $matchday = null): array
    {
        $query = [
            'status' => 'FINISHED',
        ];

        if ($matchday !== null) {
            $query['matchday'] = $matchday;
        }

        $data = $this->request(sprintf('/v4/competitions/%s/matches', $competitionCode), $query);

        return array_slice(array_map($this->normalizeMatch(...), $data['matches'] ?? []), 0, $limit);
    }

    public function getUpcomingMatches(string $competitionCode, int $limit, ?int $matchday = null): array
    {
        $query = [
            'status' => 'SCHEDULED',
        ];

        if ($matchday !== null) {
            $query['matchday'] = $matchday;
        }

        $data = $this->request(sprintf('/v4/competitions/%s/matches', $competitionCode), $query);

        return array_slice(array_map($this->normalizeMatch(...), $data['matches'] ?? []), 0, $limit);
    }

    public function getStandings(string $competitionCode): array
    {
        $data = $this->request(sprintf('/v4/competitions/%s/standings', $competitionCode));
        $table = $data['standings'][0]['table'] ?? [];

        return array_map($this->normalizeStanding(...), $table);
    }

    public function getScorers(string $competitionCode, int $limit): array
    {
        $data = $this->request(sprintf('/v4/competitions/%s/scorers', $competitionCode), [
            'limit' => $limit,
        ]);

        return array_map($this->normalizeScorer(...), $data['scorers'] ?? []);
    }

    /**
     * @param array<string, string|int> $query
     *
     * @return array<string, mixed>
     */
    private function request(string $path, array $query = []): array
    {
        try {
            $response = $this->httpClient->request('GET', $this->baseUrl.$path, [
                'headers' => [
                    'X-Auth-Token' => $this->apiToken,
                ],
                'query' => $query,
                'timeout' => $this->timeout,
                'max_duration' => $this->timeout,
            ]);

            if ($response->getStatusCode() >= 400) {
                throw FootballApiUnavailableException::unavailable();
            }

            return $response->toArray(false);
        } catch (TransportExceptionInterface|DecodingExceptionInterface) {
            throw FootballApiUnavailableException::unavailable();
        }
    }

    /**
     * @param array<string, mixed> $match
     *
     * @return array<string, mixed>
     */
    private function normalizeMatch(array $match): array
    {
        return [
            'id' => $match['id'] ?? null,
            'utcDate' => $match['utcDate'] ?? null,
            'status' => $match['status'] ?? null,
            'matchday' => $match['matchday'] ?? null,
            'homeTeam' => [
                'id' => $match['homeTeam']['id'] ?? null,
                'name' => $match['homeTeam']['name'] ?? null,
                'shortName' => $match['homeTeam']['shortName'] ?? null,
                'crest' => $match['homeTeam']['crest'] ?? null,
            ],
            'awayTeam' => [
                'id' => $match['awayTeam']['id'] ?? null,
                'name' => $match['awayTeam']['name'] ?? null,
                'shortName' => $match['awayTeam']['shortName'] ?? null,
                'crest' => $match['awayTeam']['crest'] ?? null,
            ],
            'score' => [
                'winner' => $match['score']['winner'] ?? null,
                'home' => $match['score']['fullTime']['home'] ?? null,
                'away' => $match['score']['fullTime']['away'] ?? null,
            ],
        ];
    }

    /**
     * @param array<string, mixed> $standing
     *
     * @return array<string, mixed>
     */
    private function normalizeStanding(array $standing): array
    {
        return [
            'position' => $standing['position'] ?? null,
            'team' => [
                'id' => $standing['team']['id'] ?? null,
                'name' => $standing['team']['name'] ?? null,
                'shortName' => $standing['team']['shortName'] ?? null,
                'crest' => $standing['team']['crest'] ?? null,
            ],
            'playedGames' => $standing['playedGames'] ?? 0,
            'won' => $standing['won'] ?? 0,
            'draw' => $standing['draw'] ?? 0,
            'lost' => $standing['lost'] ?? 0,
            'goalsFor' => $standing['goalsFor'] ?? 0,
            'goalsAgainst' => $standing['goalsAgainst'] ?? 0,
            'goalDifference' => $standing['goalDifference'] ?? 0,
            'points' => $standing['points'] ?? 0,
        ];
    }

    /**
     * @param array<string, mixed> $scorer
     *
     * @return array<string, mixed>
     */
    private function normalizeScorer(array $scorer): array
    {
        return [
            'player' => [
                'id' => $scorer['player']['id'] ?? null,
                'name' => $scorer['player']['name'] ?? null,
                'nationality' => $scorer['player']['nationality'] ?? null,
            ],
            'team' => [
                'id' => $scorer['team']['id'] ?? null,
                'name' => $scorer['team']['name'] ?? null,
                'shortName' => $scorer['team']['shortName'] ?? null,
                'crest' => $scorer['team']['crest'] ?? null,
            ],
            'goals' => $scorer['goals'] ?? 0,
            'assists' => $scorer['assists'] ?? null,
            'penalties' => $scorer['penalties'] ?? null,
        ];
    }
}
