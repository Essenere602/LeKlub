import { Pagination } from './feed.types';

export type SystemNotificationType =
  | 'report_rejected'
  | 'report_accepted'
  | 'warning'
  | 'suspension'
  | 'user_suspended'
  | 'user_unsuspended'
  | 'admin_role_granted'
  | 'admin_role_removed';

export type SystemNotification = {
  id: number;
  type: SystemNotificationType;
  title: string;
  message: string;
  createdAt: string;
  readAt: string | null;
};

export type PaginatedNotifications = {
  notifications: SystemNotification[];
  pagination: Pagination;
};
