import { Pagination } from './feed.types';

export type AdminOverview = {
  usersCount: number;
  postsCount: number;
  commentsCount: number;
  openReportsCount: number;
  conversationsCount: number;
  messagesCount: number;
};

export type AdminUser = {
  id: number;
  username: string;
  displayName: string | null;
  avatarUrl: string | null;
  roles: string[];
  createdAt: string;
};

export type AdminAuthor = {
  id: number;
  username: string;
  displayName: string | null;
  avatarUrl: string | null;
};

export type AdminPost = {
  id: number;
  content: string;
  author: AdminAuthor;
  likesCount: number;
  dislikesCount: number;
  commentsCount: number;
  createdAt: string;
};

export type AdminComment = {
  id: number;
  content: string;
  author: AdminAuthor;
  post: {
    id: number;
    excerpt: string;
  };
  createdAt: string;
};

export type PaginatedAdminUsers = {
  users: AdminUser[];
  pagination: Pagination;
};

export type PaginatedAdminPosts = {
  posts: AdminPost[];
  pagination: Pagination;
};

export type PaginatedAdminComments = {
  comments: AdminComment[];
  pagination: Pagination;
};

export type AdminReportStatus = 'open' | 'resolved';

export type AdminReport = {
  id: number;
  type: 'post' | 'comment';
  reason: string;
  details: string | null;
  status: AdminReportStatus;
  decision: 'rejected' | 'content_removed' | null;
  adminNote: string | null;
  reporter: AdminAuthor;
  content: {
    id: number | null;
    excerpt: string | null;
    author: AdminAuthor | null;
    deletedAt: string | null;
  };
  post: {
    id: number;
    excerpt: string;
  } | null;
  createdAt: string;
  resolvedAt: string | null;
  resolvedBy: AdminAuthor | null;
};

export type ResolveReportPayload = {
  decision: 'rejected' | 'content_removed';
  adminNote?: string | null;
};

export type PaginatedAdminReports = {
  reports: AdminReport[];
  pagination: Pagination;
};
