import { FootballCompetitionCode } from '../types/football.types';

export type AuthStackParamList = {
  Login: undefined;
  Register: undefined;
};

export type MainStackParamList = {
  Home: undefined;
  Profile: undefined;
  Feed: undefined;
  PostDetail: { postId: number };
  Football: undefined;
  FootballCompetition: { code: FootballCompetitionCode; name: string; country: string };
  Conversations: undefined;
  UserPicker: undefined;
  ConversationDetail: { conversationId: number; participantUsername: string };
};
