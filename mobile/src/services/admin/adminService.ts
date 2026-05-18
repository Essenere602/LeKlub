import { ApiResponse } from '../../types/api.types';
import {
  AdminOverview,
  PaginatedAdminComments,
  PaginatedAdminPosts,
  PaginatedAdminUsers,
} from '../../types/admin.types';
import { apiClient } from '../api/apiClient';

export const adminService = {
  async getOverview(): Promise<AdminOverview> {
    const response = await apiClient.get<ApiResponse<AdminOverview>>('/admin/overview');

    if (!response.data.data) {
      throw new Error(response.data.message ?? 'Unable to load admin overview.');
    }

    return response.data.data;
  },

  async listUsers(page = 1, limit = 10, query = ''): Promise<PaginatedAdminUsers> {
    const response = await apiClient.get<ApiResponse<PaginatedAdminUsers>>('/admin/users', {
      params: { page, limit, query },
    });

    if (!response.data.data) {
      throw new Error(response.data.message ?? 'Unable to load admin users.');
    }

    return response.data.data;
  },

  async listPosts(page = 1, limit = 10): Promise<PaginatedAdminPosts> {
    const response = await apiClient.get<ApiResponse<PaginatedAdminPosts>>('/admin/posts', {
      params: { page, limit },
    });

    if (!response.data.data) {
      throw new Error(response.data.message ?? 'Unable to load admin posts.');
    }

    return response.data.data;
  },

  async deletePost(postId: number): Promise<void> {
    await apiClient.delete<ApiResponse<null>>(`/admin/posts/${postId}`);
  },

  async listComments(page = 1, limit = 10): Promise<PaginatedAdminComments> {
    const response = await apiClient.get<ApiResponse<PaginatedAdminComments>>('/admin/comments', {
      params: { page, limit },
    });

    if (!response.data.data) {
      throw new Error(response.data.message ?? 'Unable to load admin comments.');
    }

    return response.data.data;
  },

  async deleteComment(commentId: number): Promise<void> {
    await apiClient.delete<ApiResponse<null>>(`/admin/comments/${commentId}`);
  },
};
