import { ApiResponse } from '../../types/api.types';
import { User } from '../../types/user.types';
import { apiClient } from '../api/apiClient';

type MeResponseData = {
  user: User;
};

export const userService = {
  async getCurrentUser(): Promise<User> {
    const response = await apiClient.get<ApiResponse<MeResponseData>>('/me');

    if (!response.data.data?.user) {
      throw new Error(response.data.message ?? 'Unable to load current user.');
    }

    return response.data.data.user;
  },
};
