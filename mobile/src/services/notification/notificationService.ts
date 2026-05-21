import { ApiResponse } from '../../types/api.types';
import { PaginatedNotifications, SystemNotification } from '../../types/notification.types';
import { apiClient } from '../api/apiClient';

type NotificationResponseData = {
  notification: SystemNotification;
};

export const notificationService = {
  async listNotifications(page = 1, limit = 10): Promise<PaginatedNotifications> {
    const response = await apiClient.get<ApiResponse<PaginatedNotifications>>('/me/notifications', {
      params: { page, limit },
    });

    if (!response.data.data) {
      throw new Error(response.data.message ?? 'Unable to load notifications.');
    }

    return response.data.data;
  },

  async markAsRead(notificationId: number): Promise<SystemNotification> {
    const response = await apiClient.patch<ApiResponse<NotificationResponseData>>(`/me/notifications/${notificationId}/read`);

    if (!response.data.data?.notification) {
      throw new Error(response.data.message ?? 'Unable to mark notification as read.');
    }

    return response.data.data.notification;
  },
};
