import { Pagination } from './feed.types';

export type SystemNotification = {
  id: number;
  type: 'report_rejected' | 'report_accepted' | 'warning' | 'suspension';
  title: string;
  message: string;
  createdAt: string;
  readAt: string | null;
};

export type PaginatedNotifications = {
  notifications: SystemNotification[];
  pagination: Pagination;
};
