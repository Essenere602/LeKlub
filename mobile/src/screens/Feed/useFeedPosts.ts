import { useCallback, useEffect, useState } from 'react';

import { toApiError } from '../../services/api/apiError';
import { feedService } from '../../services/feed/feedService';
import { Pagination, Post, ReactionType } from '../../types/feed.types';

const FEED_LIMIT = 10;

type UseFeedPostsResult = {
  posts: Post[];
  pagination: Pagination | null;
  error: string | null;
  isLoading: boolean;
  isRefreshing: boolean;
  isCreating: boolean;
  isLoadingMore: boolean;
  reactingPostId: number | null;
  clearError: () => void;
  createPost: (content: string) => Promise<boolean>;
  deletePost: (postId: number) => Promise<void>;
  loadMorePosts: () => Promise<void>;
  reactToPost: (postId: number, type: ReactionType) => Promise<void>;
  refreshPosts: () => Promise<void>;
  removeReaction: (postId: number) => Promise<void>;
  updatePost: (postId: number, updatedContent: string) => Promise<void>;
};

export function useFeedPosts(): UseFeedPostsResult {
  const [posts, setPosts] = useState<Post[]>([]);
  const [pagination, setPagination] = useState<Pagination | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [isRefreshing, setIsRefreshing] = useState(false);
  const [isCreating, setIsCreating] = useState(false);
  const [isLoadingMore, setIsLoadingMore] = useState(false);
  const [reactingPostId, setReactingPostId] = useState<number | null>(null);

  const loadPosts = useCallback(async (page = 1, append = false) => {
    const result = await feedService.listPosts(page, FEED_LIMIT);
    setPagination(result.pagination);
    setPosts((currentPosts) => (append ? [...currentPosts, ...result.posts] : result.posts));
  }, []);

  const loadInitialPosts = useCallback(async () => {
    setError(null);
    setIsLoading(true);

    try {
      await loadPosts();
    } catch (caughtError) {
      setError(toApiError(caughtError).message);
    } finally {
      setIsLoading(false);
    }
  }, [loadPosts]);

  useEffect(() => {
    void loadInitialPosts();
  }, [loadInitialPosts]);

  const refreshPosts = useCallback(async () => {
    setError(null);
    setIsRefreshing(true);

    try {
      await loadPosts();
    } catch (caughtError) {
      setError(toApiError(caughtError).message);
    } finally {
      setIsRefreshing(false);
    }
  }, [loadPosts]);

  const createPost = useCallback(async (content: string): Promise<boolean> => {
    const trimmedContent = content.trim();

    setError(null);

    if (!trimmedContent) {
      setError('Le contenu du post est requis.');
      return false;
    }

    setIsCreating(true);

    try {
      await feedService.createPost({ content: trimmedContent });
      await loadPosts();
      return true;
    } catch (caughtError) {
      setError(toApiError(caughtError).message);
      return false;
    } finally {
      setIsCreating(false);
    }
  }, [loadPosts]);

  const loadMorePosts = useCallback(async () => {
    if (!pagination || pagination.page >= pagination.pages || isLoadingMore) {
      return;
    }

    setIsLoadingMore(true);
    setError(null);

    try {
      await loadPosts(pagination.page + 1, true);
    } catch (caughtError) {
      setError(toApiError(caughtError).message);
    } finally {
      setIsLoadingMore(false);
    }
  }, [isLoadingMore, loadPosts, pagination]);

  const updatePostReaction = useCallback(async (postId: number, action: () => Promise<Post>) => {
    setReactingPostId(postId);
    setError(null);

    try {
      const updatedPost = await action();
      setPosts((currentPosts) => currentPosts.map((post) => (post.id === updatedPost.id ? updatedPost : post)));
    } catch (caughtError) {
      setError(toApiError(caughtError).message);
    } finally {
      setReactingPostId(null);
    }
  }, []);

  const reactToPost = useCallback(async (postId: number, type: ReactionType) => {
    await updatePostReaction(postId, () => feedService.setReaction(postId, type));
  }, [updatePostReaction]);

  const removeReaction = useCallback(async (postId: number) => {
    await updatePostReaction(postId, () => feedService.removeReaction(postId));
  }, [updatePostReaction]);

  const updatePost = useCallback(async (postId: number, updatedContent: string) => {
    setError(null);

    try {
      const updatedPost = await feedService.updatePost(postId, { content: updatedContent });
      setPosts((currentPosts) => currentPosts.map((post) => (post.id === updatedPost.id ? updatedPost : post)));
    } catch (caughtError) {
      setError(toApiError(caughtError).message);
      throw caughtError;
    }
  }, []);

  const deletePost = useCallback(async (postId: number) => {
    setError(null);

    try {
      await feedService.deletePost(postId);
      setPosts((currentPosts) => currentPosts.filter((post) => post.id !== postId));
    } catch (caughtError) {
      setError(toApiError(caughtError).message);
      throw caughtError;
    }
  }, []);

  return {
    posts,
    pagination,
    error,
    isLoading,
    isRefreshing,
    isCreating,
    isLoadingMore,
    reactingPostId,
    clearError: () => setError(null),
    createPost,
    deletePost,
    loadMorePosts,
    reactToPost,
    refreshPosts,
    removeReaction,
    updatePost,
  };
}
