export type FootballCompetitionCode = 'FL1' | 'PL' | 'PD' | 'SA' | 'BL1';

export type FootballCompetition = {
  code: FootballCompetitionCode;
  name: string;
  country: string;
};

export type FootballTeamSummary = {
  id: number | null;
  name: string | null;
  shortName: string | null;
  crest: string | null;
};

export type FootballMatch = {
  id: number | null;
  utcDate: string | null;
  status: string | null;
  matchday: number | null;
  homeTeam: FootballTeamSummary;
  awayTeam: FootballTeamSummary;
  score: {
    winner: string | null;
    home: number | null;
    away: number | null;
  };
};

export type FootballStanding = {
  position: number | null;
  team: FootballTeamSummary;
  playedGames: number;
  won: number;
  draw: number;
  lost: number;
  goalsFor: number;
  goalsAgainst: number;
  goalDifference: number;
  points: number;
};

export type FootballPlayerSummary = {
  id: number | null;
  name: string | null;
  nationality: string | null;
};

export type FootballScorer = {
  player: FootballPlayerSummary;
  team: FootballTeamSummary;
  goals: number;
  assists: number | null;
  penalties: number | null;
};
