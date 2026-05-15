import { FootballCompetitionCode } from '../types/football.types';

export type FootballCompetitionVisual = {
  accentColor: string;
  countryCode: string;
  flag: string;
};

export const footballCompetitionVisuals: Record<FootballCompetitionCode, FootballCompetitionVisual> = {
  BL1: {
    accentColor: '#FFCC00',
    countryCode: 'DE',
    flag: '🇩🇪',
  },
  FL1: {
    accentColor: '#C8FF00',
    countryCode: 'FR',
    flag: '🇫🇷',
  },
  PD: {
    accentColor: '#FF3B5C',
    countryCode: 'ES',
    flag: '🇪🇸',
  },
  PL: {
    accentColor: '#00CCFF',
    countryCode: 'EN',
    flag: '🇬🇧',
  },
  SA: {
    accentColor: '#39FF14',
    countryCode: 'IT',
    flag: '🇮🇹',
  },
};
