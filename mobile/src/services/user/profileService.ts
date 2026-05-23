import { ApiResponse } from '../../types/api.types';
import { ChangePasswordPayload, UpdateAccountPayload, UpdateProfilePayload, User } from '../../types/user.types';
import { apiClient } from '../api/apiClient';

type UpdateProfileResponseData = {
  user: User;
};

type UpdateAccountResponseData = {
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

  async updateCurrentAccount(payload: UpdateAccountPayload): Promise<User> {
    const response = await apiClient.patch<ApiResponse<UpdateAccountResponseData>>('/me/account', payload);

    if (!response.data.data?.user) {
      throw new Error(response.data.message ?? 'Unable to update account.');
    }

    return response.data.data.user;
  },

  async changePassword(payload: ChangePasswordPayload): Promise<void> {
    await apiClient.patch<ApiResponse<[]>>('/me/password', payload);
  },
};
