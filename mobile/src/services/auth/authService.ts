import { apiClient } from '../api/apiClient';
import { tokenStorage } from './tokenStorage';
import { LoginCredentials, LoginResponse, RegisterPayload } from '../../types/auth.types';
import { ApiResponse } from '../../types/api.types';
import { User } from '../../types/user.types';
import { userService } from '../user/userService';

type RegisterResponseData = {
  user: User;
};

export const authService = {
  async login(credentials: LoginCredentials): Promise<string> {
    const response = await apiClient.post<LoginResponse>('/auth/login', credentials);
    await tokenStorage.setAccessToken(response.data.token);

    return response.data.token;
  },

  async register(payload: RegisterPayload): Promise<User> {
    const response = await apiClient.post<ApiResponse<RegisterResponseData>>('/auth/register', payload);

    if (!response.data.data?.user) {
      throw new Error(response.data.message ?? 'Registration failed.');
    }

    return response.data.data.user;
  },

  async logout(): Promise<void> {
    await tokenStorage.clearAccessToken();
  },

  async getCurrentUser(): Promise<User> {
    return userService.getCurrentUser();
  },
};
