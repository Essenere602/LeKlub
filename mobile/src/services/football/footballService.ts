import { ApiResponse } from '../../types/api.types';
import {
  FootballCompetition,
  FootballCompetitionCode,
  FootballMatch,
  FootballScorer,
  FootballStanding,
} from '../../types/football.types';
import { apiClient } from '../api/apiClient';

type CompetitionsResponseData = {
  competitions: FootballCompetition[];
};

type MatchesResponseData = {
  matches: FootballMatch[];
};

type StandingsResponseData = {
  standings: FootballStanding[];
};

type ScorersResponseData = {
  scorers: FootballScorer[];
};

const DEFAULT_LIMIT = 10;

export const footballService = {
  async listCompetitions(): Promise<FootballCompetition[]> {
    const response = await apiClient.get<ApiResponse<CompetitionsResponseData>>('/football/competitions');

    if (!response.data.data?.competitions) {
      throw new Error(response.data.message ?? 'Unable to load football competitions.');
    }

    return response.data.data.competitions;
  },

  async listResults(code: FootballCompetitionCode, limit = DEFAULT_LIMIT, matchday?: number): Promise<FootballMatch[]> {
    const response = await apiClient.get<ApiResponse<MatchesResponseData>>(`/football/competitions/${code}/results`, {
      params: matchday ? { limit, matchday } : { limit },
    });

    if (!response.data.data?.matches) {
      throw new Error(response.data.message ?? 'Unable to load football results.');
    }

    return response.data.data.matches;
  },

  async listUpcomingMatches(code: FootballCompetitionCode, limit = DEFAULT_LIMIT, matchday?: number): Promise<FootballMatch[]> {
    const response = await apiClient.get<ApiResponse<MatchesResponseData>>(`/football/competitions/${code}/upcoming`, {
      params: matchday ? { limit, matchday } : { limit },
    });

    if (!response.data.data?.matches) {
      throw new Error(response.data.message ?? 'Unable to load upcoming football matches.');
    }

    return response.data.data.matches;
  },

  async listStandings(code: FootballCompetitionCode): Promise<FootballStanding[]> {
    const response = await apiClient.get<ApiResponse<StandingsResponseData>>(`/football/competitions/${code}/standings`);

    if (!response.data.data?.standings) {
      throw new Error(response.data.message ?? 'Unable to load football standings.');
    }

    return response.data.data.standings;
  },

  async listScorers(code: FootballCompetitionCode, limit = DEFAULT_LIMIT): Promise<FootballScorer[]> {
    const response = await apiClient.get<ApiResponse<ScorersResponseData>>(`/football/competitions/${code}/scorers`, {
      params: { limit },
    });

    if (!response.data.data?.scorers) {
      throw new Error(response.data.message ?? 'Unable to load football scorers.');
    }

    return response.data.data.scorers;
  },
};
