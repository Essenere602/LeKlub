import { ApiResponse } from '../../types/api.types';
import {
  Commentaire,
  CreateCommentPayload,
  CreatePostPayload,
  PaginatedComments,
  PaginatedPosts,
  Post,
  ReactionType,
} from '../../types/feed.types';
import { apiClient } from '../api/apiClient';

type PostResponseData = {
  post: Post;
};

type CommentResponseData = {
  comment: Commentaire;
};

export const feedService = {
  async listPosts(page = 1, limit = 10): Promise<PaginatedPosts> {
    const response = await apiClient.get<ApiResponse<PaginatedPosts>>('/feed', {
      params: { page, limit },
    });

    if (!response.data.data) {
      throw new Error(response.data.message ?? 'Unable to load feed.');
    }

    return response.data.data;
  },

  async createPost(payload: CreatePostPayload): Promise<Post> {
    const response = await apiClient.post<ApiResponse<PostResponseData>>('/feed', payload);

    if (!response.data.data?.post) {
      throw new Error(response.data.message ?? 'Unable to create post.');
    }

    return response.data.data.post;
  },

  async getPost(postId: number): Promise<Post> {
    const response = await apiClient.get<ApiResponse<PostResponseData>>(`/feed/${postId}`);

    if (!response.data.data?.post) {
      throw new Error(response.data.message ?? 'Unable to load post.');
    }

    return response.data.data.post;
  },

  async listComments(postId: number, page = 1, limit = 20): Promise<PaginatedComments> {
    const response = await apiClient.get<ApiResponse<PaginatedComments>>(`/feed/${postId}/comments`, {
      params: { page, limit },
    });

    if (!response.data.data) {
      throw new Error(response.data.message ?? 'Unable to load comments.');
    }

    return response.data.data;
  },

  async createComment(postId: number, payload: CreateCommentPayload): Promise<Commentaire> {
    const response = await apiClient.post<ApiResponse<CommentResponseData>>(`/feed/${postId}/comments`, payload);

    if (!response.data.data?.comment) {
      throw new Error(response.data.message ?? 'Unable to create comment.');
    }

    return response.data.data.comment;
  },

  async setReaction(postId: number, type: ReactionType): Promise<Post> {
    const response = await apiClient.put<ApiResponse<PostResponseData>>(`/feed/${postId}/reaction`, { type });

    if (!response.data.data?.post) {
      throw new Error(response.data.message ?? 'Unable to save reaction.');
    }

    return response.data.data.post;
  },

  async removeReaction(postId: number): Promise<Post> {
    const response = await apiClient.delete<ApiResponse<PostResponseData>>(`/feed/${postId}/reaction`);

    if (!response.data.data?.post) {
      throw new Error(response.data.message ?? 'Unable to remove reaction.');
    }

    return response.data.data.post;
  },
};
