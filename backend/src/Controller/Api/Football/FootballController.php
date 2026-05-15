<?php

declare(strict_types=1);

namespace App\Controller\Api\Football;

use App\Application\Football\GetFootballStandingsUseCase;
use App\Application\Football\ListFootballCompetitionsUseCase;
use App\Application\Football\ListFootballResultsUseCase;
use App\Application\Football\ListFootballScorersUseCase;
use App\Application\Football\ListUpcomingFootballMatchesUseCase;
use App\Domain\Exception\FootballApiUnavailableException;
use App\Domain\Exception\UnsupportedFootballCompetitionException;
use App\Shared\Api\ApiResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/football')]
final class FootballController
{
    #[Route('/competitions', name: 'api_football_competitions', methods: ['GET'])]
    public function competitions(ListFootballCompetitionsUseCase $useCase): JsonResponse
    {
        return ApiResponse::success([
            'competitions' => $useCase->execute(),
        ]);
    }

    #[Route('/competitions/{code}/results', name: 'api_football_results', methods: ['GET'])]
    public function results(string $code, Request $request, ListFootballResultsUseCase $useCase): JsonResponse
    {
        try {
            return ApiResponse::success([
                'matches' => $useCase->execute(
                    $code,
                    $request->query->getInt('limit', 10),
                    $request->query->getInt('matchday', 0),
                ),
            ]);
        } catch (UnsupportedFootballCompetitionException) {
            return ApiResponse::error('Competition not supported.', [], 404);
        } catch (FootballApiUnavailableException) {
            return $this->footballApiUnavailableResponse();
        }
    }

    #[Route('/competitions/{code}/upcoming', name: 'api_football_upcoming', methods: ['GET'])]
    public function upcoming(string $code, Request $request, ListUpcomingFootballMatchesUseCase $useCase): JsonResponse
    {
        try {
            return ApiResponse::success([
                'matches' => $useCase->execute(
                    $code,
                    $request->query->getInt('limit', 10),
                    $request->query->getInt('matchday', 0),
                ),
            ]);
        } catch (UnsupportedFootballCompetitionException) {
            return ApiResponse::error('Competition not supported.', [], 404);
        } catch (FootballApiUnavailableException) {
            return $this->footballApiUnavailableResponse();
        }
    }

    #[Route('/competitions/{code}/standings', name: 'api_football_standings', methods: ['GET'])]
    public function standings(string $code, GetFootballStandingsUseCase $useCase): JsonResponse
    {
        try {
            return ApiResponse::success([
                'standings' => $useCase->execute($code),
            ]);
        } catch (UnsupportedFootballCompetitionException) {
            return ApiResponse::error('Competition not supported.', [], 404);
        } catch (FootballApiUnavailableException) {
            return $this->footballApiUnavailableResponse();
        }
    }

    #[Route('/competitions/{code}/scorers', name: 'api_football_scorers', methods: ['GET'])]
    public function scorers(string $code, Request $request, ListFootballScorersUseCase $useCase): JsonResponse
    {
        try {
            return ApiResponse::success([
                'scorers' => $useCase->execute($code, $request->query->getInt('limit', 10)),
            ]);
        } catch (UnsupportedFootballCompetitionException) {
            return ApiResponse::error('Competition not supported.', [], 404);
        } catch (FootballApiUnavailableException) {
            return $this->footballApiUnavailableResponse();
        }
    }

    private function footballApiUnavailableResponse(): JsonResponse
    {
        return ApiResponse::error(
            'Football data is temporarily unavailable. Please try again later.',
            [],
            502
        );
    }
}
