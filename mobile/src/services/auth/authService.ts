import { apiClient } from '../api/apiClient';
import { tokenStorage } from './tokenStorage';
import {
  ForgotPasswordPayload,
  LoginCredentials,
  LoginResponse,
  RegisterPayload,
  ResetPasswordPayload,
} from '../../types/auth.types';
import { ApiResponse } from '../../types/api.types';
import { User } from '../../types/user.types';
import { userService } from '../user/userService';

type RegisterResponseData = {
  user: User;
};

export const authService = {
  async login(credentials: LoginCredentials): Promise<string> {
    const response = await apiClient.post<LoginResponse>('/auth/login', credentials);
    await tokenStorage.setTokens(response.data.token, response.data.refreshToken);

    return response.data.token;
  },

  async register(payload: RegisterPayload): Promise<User> {
    const response = await apiClient.post<ApiResponse<RegisterResponseData>>('/auth/register', payload);

    if (!response.data.data?.user) {
      throw new Error(response.data.message ?? 'Registration failed.');
    }

    return response.data.data.user;
  },

  async forgotPassword(payload: ForgotPasswordPayload): Promise<void> {
    await apiClient.post<ApiResponse<[]>>('/auth/forgot-password', payload);
  },

  async resetPassword(payload: ResetPasswordPayload): Promise<void> {
    await apiClient.post<ApiResponse<[]>>('/auth/reset-password', payload);
  },

  async logout(): Promise<void> {
    const refreshToken = await tokenStorage.getRefreshToken();

    try {
      if (refreshToken) {
        await apiClient.post<ApiResponse<[]>>('/auth/logout', { refreshToken });
      }
    } finally {
      await tokenStorage.clearTokens();
    }
  },

  async getCurrentUser(): Promise<User> {
    return userService.getCurrentUser();
  },
};
