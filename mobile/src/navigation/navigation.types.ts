import { FootballCompetitionCode } from '../types/football.types';

export type AuthStackParamList = {
  Login: undefined;
  Register: undefined;
  ForgotPassword: undefined;
  ResetPassword: { email?: string };
};

export type MainTabParamList = {
  Home: undefined;
  FeedTab: undefined;
  FootballTab: undefined;
  MessagingTab: undefined;
  ProfileTab: undefined;
  AdminTab: undefined;
};

export type HomeStackParamList = {
  Home: undefined;
};

export type FeedStackParamList = {
  Feed: undefined;
  PostDetail: { postId: number };
};

export type FootballStackParamList = {
  Football: undefined;
  FootballCompetition: { code: FootballCompetitionCode; name: string; country: string };
};

export type MessagingStackParamList = {
  Conversations: undefined;
  UserPicker: undefined;
  ConversationDetail: { conversationId: number; participantAvatarUrl?: string | null; participantUsername: string };
};

export type ProfileStackParamList = {
  Profile: undefined;
  EditAccount: undefined;
  EditProfile: undefined;
  ChangePassword: undefined;
  Notifications: undefined;
};

export type AdminStackParamList = {
  AdminHome: undefined;
  AdminUsers: undefined;
  AdminFeedModeration: undefined;
  AdminReports: undefined;
  AdminWarnings: undefined;
};
