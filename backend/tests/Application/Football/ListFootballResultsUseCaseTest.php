<?php

declare(strict_types=1);

namespace App\Tests\Application\Football;

use App\Application\Football\ListFootballResultsUseCase;
use App\Domain\Exception\UnsupportedFootballCompetitionException;
use App\Infrastructure\Football\FootballDataClientInterface;
use PHPUnit\Framework\TestCase;

final class ListFootballResultsUseCaseTest extends TestCase
{
    public function testItLimitsAndDelegatesSupportedCompetition(): void
    {
        $client = new FakeFootballDataClient();
        $useCase = new ListFootballResultsUseCase($client);

        $useCase->execute('fl1', 50, 34);

        self::assertSame('FL1', $client->competitionCode);
        self::assertSame(20, $client->limit);
        self::assertSame(34, $client->matchday);
    }

    public function testItRejectsUnsupportedCompetition(): void
    {
        $useCase = new ListFootballResultsUseCase(new FakeFootballDataClient());

        $this->expectException(UnsupportedFootballCompetitionException::class);

        $useCase->execute('MLS', 10);
    }
}

final class FakeFootballDataClient implements FootballDataClientInterface
{
    public ?string $competitionCode = null;
    public ?int $limit = null;
    public ?int $matchday = null;

    public function getResults(string $competitionCode, int $limit, ?int $matchday = null): array
    {
        $this->competitionCode = $competitionCode;
        $this->limit = $limit;
        $this->matchday = $matchday;

        return [];
    }

    public function getUpcomingMatches(string $competitionCode, int $limit, ?int $matchday = null): array
    {
        return [];
    }

    public function getStandings(string $competitionCode): array
    {
        return [];
    }

    public function getScorers(string $competitionCode, int $limit): array
    {
        return [];
    }
}
