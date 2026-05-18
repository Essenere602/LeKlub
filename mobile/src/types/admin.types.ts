import { Pagination } from './feed.types';

export type AdminOverview = {
  usersCount: number;
  postsCount: number;
  commentsCount: number;
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
