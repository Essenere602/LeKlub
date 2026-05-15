export type ReactionType = 'like' | 'dislike';

export type FeedAuthor = {
  id: number;
  username: string;
};

export type Post = {
  id: number;
  content: string;
  author: FeedAuthor;
  likesCount: number;
  dislikesCount: number;
  commentsCount: number;
  createdAt: string;
};

export type Commentaire = {
  id: number;
  content: string;
  author: FeedAuthor;
  createdAt: string;
};

export type Pagination = {
  page: number;
  limit: number;
  total: number;
  pages: number;
};

export type PaginatedPosts = {
  posts: Post[];
  pagination: Pagination;
};

export type PaginatedComments = {
  comments: Commentaire[];
  pagination: Pagination;
};

export type CreatePostPayload = {
  content: string;
};

export type CreateCommentPayload = {
  content: string;
};
