export type User = {
  id: number;
  email: string;
  username: string;
  roles: string[];
  profile: UserProfile;
  createdAt: string;
};

export type UserProfile = {
  displayName: string | null;
  bio: string | null;
  favoriteTeamName: string | null;
  avatarUrl: string | null;
};

export type UpdateProfilePayload = Partial<UserProfile>;

export type ChangePasswordPayload = {
  currentPassword: string;
  newPassword: string;
  newPasswordConfirmation: string;
};
