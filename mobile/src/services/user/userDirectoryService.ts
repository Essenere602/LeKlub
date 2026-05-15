import { ApiResponse } from '../../types/api.types';
import { MessagingUser } from '../../types/userDirectory.types';
import { apiClient } from '../api/apiClient';

type UserDirectoryResponseData = {
  users: MessagingUser[];
};

export const userDirectoryService = {
  async listUsers(query = '', limit = 20): Promise<MessagingUser[]> {
    const response = await apiClient.get<ApiResponse<UserDirectoryResponseData>>('/users', {
      params: { query, limit },
    });

    if (!response.data.data?.users) {
      throw new Error(response.data.message ?? 'Unable to load users.');
    }

    return response.data.data.users;
  },
};
