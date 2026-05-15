<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Football;

use App\Domain\Exception\FootballApiUnavailableException;
use App\Infrastructure\Football\ExternalFootballDataClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class ExternalFootballDataClientTest extends TestCase
{
    public function testItReturnsNormalizedFootballResults(): void
    {
        $client = new ExternalFootballDataClient(
            new MockHttpClient(new MockResponse(json_encode([
                'matches' => [
                    [
                        'id' => 10,
                        'utcDate' => '2026-05-15T20:00:00Z',
                        'status' => 'FINISHED',
                        'matchday' => 34,
                        'homeTeam' => [
                            'id' => 1,
                            'name' => 'Paris',
                            'shortName' => 'PSG',
                            'crest' => 'https://example.com/psg.png',
                        ],
                        'awayTeam' => [
                            'id' => 2,
                            'name' => 'Lyon',
                            'shortName' => 'OL',
                            'crest' => 'https://example.com/ol.png',
                        ],
                        'score' => [
                            'winner' => 'HOME_TEAM',
                            'fullTime' => [
                                'home' => 2,
                                'away' => 1,
                            ],
                        ],
                        'extraRawField' => 'must not leak',
                    ],
                ],
            ], JSON_THROW_ON_ERROR))),
            'https://api.football-data.org',
            'test-token',
            3.0,
        );

        $results = $client->getResults('FL1', 10);

        self::assertSame(10, $results[0]['id']);
        self::assertSame('Paris', $results[0]['homeTeam']['name']);
        self::assertSame(2, $results[0]['score']['home']);
        self::assertArrayNotHasKey('extraRawField', $results[0]);
    }

    public function testItFiltersResultsByMatchdayWhenProvided(): void
    {
        $capturedOptions = [];
        $client = new ExternalFootballDataClient(
            new MockHttpClient(static function (string $method, string $url, array $options) use (&$capturedOptions): MockResponse {
                $capturedOptions = $options;

                return new MockResponse('{"matches":[]}');
            }),
            'https://api.football-data.org',
            'test-token',
            3.0,
        );

        $client->getResults('PL', 20, 12);

        self::assertSame([
            'status' => 'FINISHED',
            'matchday' => 12,
        ], $capturedOptions['query']);
    }

    public function testItThrowsCleanExceptionOnFootballApiError(): void
    {
        $client = new ExternalFootballDataClient(
            new MockHttpClient(new MockResponse('{"message":"Forbidden"}', ['http_code' => 403])),
            'https://api.football-data.org',
            'test-token',
            3.0,
        );

        $this->expectException(FootballApiUnavailableException::class);
        $this->expectExceptionMessage('FOOTBALL_API_UNAVAILABLE');

        $client->getStandings('FL1');
    }

    public function testItThrowsCleanExceptionOnTimeout(): void
    {
        $client = new ExternalFootballDataClient(
            new MockHttpClient(static fn (string $method, string $url, array $options): MockResponse => throw new TransportException('Timeout')),
            'https://api.football-data.org',
            'test-token',
            0.1,
        );

        $this->expectException(FootballApiUnavailableException::class);
        $this->expectExceptionMessage('FOOTBALL_API_UNAVAILABLE');

        $client->getUpcomingMatches('PL', 10);
    }
}
