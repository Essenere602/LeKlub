import { ApiResponse } from '../../types/api.types';
import { UpdateProfilePayload, User } from '../../types/user.types';
import { apiClient } from '../api/apiClient';

type UpdateProfileResponseData = {
  user: User;
};

export const profileService = {
  async updateCurrentProfile(payload: UpdateProfilePayload): Promise<User> {
    const response = await apiClient.patch<ApiResponse<UpdateProfileResponseData>>('/me/profile', payload);

    if (!response.data.data?.user) {
      throw new Error(response.data.message ?? 'Unable to update profile.');
    }

    return response.data.data.user;
  },
};
