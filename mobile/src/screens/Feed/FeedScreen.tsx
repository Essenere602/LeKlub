import { useCallback, useEffect, useState } from 'react';
import { ActivityIndicator, FlatList, RefreshControl, StyleSheet, View } from 'react-native';
import { NativeStackScreenProps } from '@react-navigation/native-stack';

import { PostCard } from '../../components/feed/PostCard';
import { AppButton } from '../../components/ui/AppButton';
import { AppInput } from '../../components/ui/AppInput';
import { AppText } from '../../components/ui/AppText';
import { ErrorMessage } from '../../components/ui/ErrorMessage';
import { Screen } from '../../components/ui/Screen';
import { theme } from '../../config/theme';
import { MainStackParamList } from '../../navigation/navigation.types';
import { toApiError } from '../../services/api/apiError';
import { feedService } from '../../services/feed/feedService';
import { Pagination, Post, ReactionType } from '../../types/feed.types';

type FeedScreenProps = NativeStackScreenProps<MainStackParamList, 'Feed'>;

const FEED_LIMIT = 10;

export function FeedScreen({ navigation }: FeedScreenProps) {
  const [posts, setPosts] = useState<Post[]>([]);
  const [pagination, setPagination] = useState<Pagination | null>(null);
  const [content, setContent] = useState('');
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

  return (
    <Screen style={styles.screen}>
      <FlatList
        contentContainerStyle={styles.content}
        data={posts}
        keyExtractor={(post) => String(post.id)}
        ListEmptyComponent={!isLoading ? <EmptyFeed /> : null}
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
            <View style={styles.titleBlock}>
              <AppText style={styles.kicker}>LeKlub Feed</AppText>
              <AppText variant="title">Feed</AppText>
              <AppText variant="subtitle">Partage un message court avec les autres membres.</AppText>
            </View>

            <View style={styles.createPanel}>
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
            </View>

            <ErrorMessage message={error} />
            {isLoading ? <ActivityIndicator color={theme.colors.accent} /> : null}
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
            disabled={reactingPostId === item.id}
            onOpen={() => navigation.navigate('PostDetail', { postId: item.id })}
            onReact={(type) => reactToPost(item.id, type)}
            onRemoveReaction={() => removeReaction(item.id)}
            post={item}
          />
        )}
      />
    </Screen>
  );
}

function EmptyFeed() {
  return (
    <View style={styles.empty}>
      <AppText variant="label">Aucun post pour le moment.</AppText>
      <AppText variant="muted">Publie le premier message du Klub.</AppText>
    </View>
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
  titleBlock: {
    gap: theme.spacing.sm,
  },
  kicker: {
    color: theme.colors.accent,
    fontSize: theme.typography.sizes.sm,
    fontWeight: theme.typography.weights.bold,
    textTransform: 'uppercase',
  },
  createPanel: {
    backgroundColor: theme.colors.surface,
    borderColor: theme.colors.border,
    borderRadius: theme.radius.md,
    borderWidth: 1,
    gap: theme.spacing.md,
    padding: theme.spacing.lg,
  },
  textArea: {
    minHeight: 96,
    paddingTop: theme.spacing.md,
  },
  empty: {
    alignItems: 'center',
    backgroundColor: theme.colors.surface,
    borderColor: theme.colors.border,
    borderRadius: theme.radius.md,
    borderWidth: 1,
    gap: theme.spacing.sm,
    padding: theme.spacing.xl,
  },
});
