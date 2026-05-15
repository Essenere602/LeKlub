export type LoginCredentials = {
  email: string;
  password: string;
};

export type RegisterPayload = {
  email: string;
  username: string;
  password: string;
};

export type LoginResponse = {
  token: string;
};
