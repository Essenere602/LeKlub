import { useCallback, useEffect, useState } from 'react';
import { Alert, FlatList, RefreshControl, StyleSheet, View } from 'react-native';
import { NativeStackScreenProps } from '@react-navigation/native-stack';

import { PostCard } from '../../components/feed/PostCard';
import { ReportContentModal } from '../../components/feed/ReportContentModal';
import { AppButton } from '../../components/ui/AppButton';
import { AppCard } from '../../components/ui/AppCard';
import { AppHeader } from '../../components/ui/AppHeader';
import { AppInput } from '../../components/ui/AppInput';
import { EmptyState } from '../../components/ui/EmptyState';
import { ErrorState } from '../../components/ui/ErrorState';
import { LoadingState } from '../../components/ui/LoadingState';
import { Screen } from '../../components/ui/Screen';
import { theme } from '../../config/theme';
import { useAuth } from '../../hooks/useAuth';
import { FeedStackParamList } from '../../navigation/navigation.types';
import { toApiError } from '../../services/api/apiError';
import { feedService } from '../../services/feed/feedService';
import { CreateReportPayload, Pagination, Post, ReactionType } from '../../types/feed.types';

type FeedScreenProps = NativeStackScreenProps<FeedStackParamList, 'Feed'>;

const FEED_LIMIT = 10;

export function FeedScreen({ navigation }: FeedScreenProps) {
  const { user } = useAuth();
  const [posts, setPosts] = useState<Post[]>([]);
  const [pagination, setPagination] = useState<Pagination | null>(null);
  const [content, setContent] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [isRefreshing, setIsRefreshing] = useState(false);
  const [isCreating, setIsCreating] = useState(false);
  const [isLoadingMore, setIsLoadingMore] = useState(false);
  const [reactingPostId, setReactingPostId] = useState<number | null>(null);
  const [reportingPostId, setReportingPostId] = useState<number | null>(null);
  const [isReporting, setIsReporting] = useState(false);
  const [reportError, setReportError] = useState<string | null>(null);

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

  async function refreshPosts() {
    setError(null);
    setIsRefreshing(true);

    try {
      await loadPosts();
    } catch (caughtError) {
      setError(toApiError(caughtError).message);
    } finally {
      setIsRefreshing(false);
    }
  }

  async function createPost() {
    const trimmedContent = content.trim();

    setError(null);

    if (!trimmedContent) {
      setError('Le contenu du post est requis.');
      return;
    }

    setIsCreating(true);

    try {
      await feedService.createPost({ content: trimmedContent });
      setContent('');
      await loadPosts();
    } catch (caughtError) {
      setError(toApiError(caughtError).message);
    } finally {
      setIsCreating(false);
    }
  }

  async function loadMorePosts() {
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
  }

  async function reactToPost(postId: number, type: ReactionType) {
    await updatePostReaction(postId, () => feedService.setReaction(postId, type));
  }

  async function removeReaction(postId: number) {
    await updatePostReaction(postId, () => feedService.removeReaction(postId));
  }

  async function updatePost(postId: number, updatedContent: string) {
    setError(null);

    try {
      const updatedPost = await feedService.updatePost(postId, { content: updatedContent });
      setPosts((currentPosts) => currentPosts.map((post) => (post.id === updatedPost.id ? updatedPost : post)));
    } catch (caughtError) {
      setError(toApiError(caughtError).message);
      throw caughtError;
    }
  }

  async function deletePost(postId: number) {
    setError(null);

    try {
      await feedService.deletePost(postId);
      setPosts((currentPosts) => currentPosts.filter((post) => post.id !== postId));
    } catch (caughtError) {
      setError(toApiError(caughtError).message);
      throw caughtError;
    }
  }

  async function updatePostReaction(postId: number, action: () => Promise<Post>) {
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
  }

  async function submitReport(payload: CreateReportPayload) {
    if (reportingPostId === null) {
      return;
    }

    setIsReporting(true);
    setReportError(null);

    try {
      await feedService.reportPost(reportingPostId, payload);
      setReportingPostId(null);
      Alert.alert('Signalement envoyé', "L'équipe de modération pourra traiter ce contenu.");
    } catch (caughtError) {
      setReportError(toApiError(caughtError).message);
      throw caughtError;
    } finally {
      setIsReporting(false);
    }
  }

  return (
    <Screen style={styles.screen}>
      <ReportContentModal
        error={reportError}
        loading={isReporting}
        targetLabel="Post"
        visible={reportingPostId !== null}
        onClose={() => {
          setReportingPostId(null);
          setReportError(null);
        }}
        onSubmit={submitReport}
      />
      <FlatList
        contentContainerStyle={styles.content}
        data={posts}
        keyExtractor={(post) => String(post.id)}
        ListEmptyComponent={!isLoading && !error ? (
          <EmptyState
            actionLabel="Rafraîchir"
            icon="chatbubble-ellipses-outline"
            message="Publie le premier message du Klub."
            title="Aucun post pour le moment"
            onAction={refreshPosts}
          />
        ) : null}
        ListFooterComponent={
          pagination && pagination.page < pagination.pages ? (
            <AppButton
              label="Charger plus"
              loading={isLoadingMore}
              onPress={loadMorePosts}
              variant="secondary"
            />
          ) : null
        }
        ListHeaderComponent={
          <View style={styles.header}>
            <AppHeader
              kicker="LeKlub Feed"
              subtitle="Partage un message court avec les autres membres."
              title="Feed"
            />

            <AppCard style={styles.createPanel}>
              <AppInput
                autoCapitalize="sentences"
                label="Nouveau post"
                maxLength={1000}
                multiline
                onChangeText={setContent}
                placeholder="Ton analyse du match, une actu, une réaction..."
                style={styles.textArea}
                textAlignVertical="top"
                value={content}
              />
              <AppButton label="Publier" loading={isCreating} onPress={createPost} />
            </AppCard>

            {error ? <ErrorState message={error} onRetry={loadInitialPosts} /> : null}
            {isLoading ? <LoadingState message="Chargement du Feed..." /> : null}
          </View>
        }
        refreshControl={
          <RefreshControl
            refreshing={isRefreshing}
            tintColor={theme.colors.accent}
            onRefresh={refreshPosts}
          />
        }
        renderItem={({ item }) => (
          <PostCard
            canManage={item.author.id === user?.id}
            disabled={reactingPostId === item.id}
            onOpen={() => navigation.navigate('PostDetail', { postId: item.id })}
            onReact={(type) => reactToPost(item.id, type)}
            onDelete={() => deletePost(item.id)}
            onReport={() => setReportingPostId(item.id)}
            onRemoveReaction={() => removeReaction(item.id)}
            onUpdate={(updatedContent) => updatePost(item.id, updatedContent)}
            post={item}
          />
        )}
      />
    </Screen>
  );
}

const styles = StyleSheet.create({
  screen: {
    padding: 0,
  },
  content: {
    gap: theme.spacing.lg,
    padding: theme.spacing.xl,
  },
  header: {
    gap: theme.spacing.lg,
  },
  createPanel: {
    gap: theme.spacing.md,
  },
  textArea: {
    minHeight: 96,
    paddingTop: theme.spacing.md,
  },
});
