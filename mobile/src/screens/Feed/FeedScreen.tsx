import { useCallback, useEffect, useState } from 'react';
import { Alert, FlatList, RefreshControl, StyleSheet, View } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { NativeStackScreenProps } from '@react-navigation/native-stack';
import { useBottomTabBarHeight } from '@react-navigation/bottom-tabs';

import { PostCard } from '../../components/feed/PostCard';
import { ReportContentModal } from '../../components/feed/ReportContentModal';
import { AppButton } from '../../components/ui/AppButton';
import { AppCard } from '../../components/ui/AppCard';
import { AppInput } from '../../components/ui/AppInput';
import { AppText } from '../../components/ui/AppText';
import { EmptyState } from '../../components/ui/EmptyState';
import { ErrorMessage } from '../../components/ui/ErrorMessage';
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
  const tabBarHeight = useBottomTabBarHeight();
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
        contentContainerStyle={[styles.content, { paddingBottom: tabBarHeight + theme.spacing['2xl'] }]}
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
              <AppText variant="title">Le terrain des supporters</AppText>
              <AppText variant="subtitle">Réactions, débats et humeurs de match entre membres du Klub.</AppText>
            </View>

            <AppCard variant="accent" style={styles.createPanel}>
              <View style={styles.composerHeader}>
                <View style={styles.composerAvatar}>
                  <AppText style={styles.composerAvatarText}>
                    {(user?.profile.displayName ?? user?.username ?? 'K').slice(0, 1).toUpperCase()}
                  </AppText>
                </View>
                <View style={styles.composerTitle}>
                  <AppText style={styles.composerLabel}>Créer un Post</AppText>
                  <AppText variant="muted">Partage une réaction football avec le Klub.</AppText>
                </View>
              </View>
              <AppInput
                autoCapitalize="sentences"
                label="Message"
                maxLength={1000}
                multiline
                onChangeText={setContent}
                placeholder="Ton analyse du match, une actu, une réaction..."
                style={styles.textArea}
                textAlignVertical="top"
                value={content}
              />
              <View style={styles.composerFooter}>
                <View style={styles.composerHint}>
                  <Ionicons color={theme.colors.text.muted} name="football-outline" size={16} />
                  <AppText variant="muted">{content.trim().length}/1000</AppText>
                </View>
                <AppButton label="Publier" loading={isCreating} onPress={createPost} style={styles.publishButton} />
              </View>
            </AppCard>

            <ErrorMessage message={error} />
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

function EmptyFeed() {
  return (
    <EmptyState
      icon="chatbubbles-outline"
      message="Publie le premier message du Klub et lance la discussion."
      title="Aucun Post pour le moment"
    />
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
    gap: theme.spacing.md,
  },
  composerHeader: {
    alignItems: 'center',
    flexDirection: 'row',
    gap: theme.spacing.md,
  },
  composerAvatar: {
    alignItems: 'center',
    backgroundColor: theme.colors.accent,
    borderRadius: 22,
    height: 44,
    justifyContent: 'center',
    width: 44,
  },
  composerAvatarText: {
    color: theme.colors.text.inverse,
    fontSize: theme.typography.sizes.lg,
    fontWeight: theme.typography.weights.bold,
    includeFontPadding: false,
    lineHeight: 20,
  },
  composerTitle: {
    flex: 1,
    gap: theme.spacing.xs,
  },
  composerLabel: {
    fontSize: theme.typography.sizes.md,
    fontWeight: theme.typography.weights.bold,
  },
  textArea: {
    minHeight: 96,
    paddingTop: theme.spacing.md,
  },
  composerFooter: {
    alignItems: 'center',
    flexDirection: 'row',
    gap: theme.spacing.md,
    justifyContent: 'space-between',
  },
  composerHint: {
    alignItems: 'center',
    flexDirection: 'row',
    gap: theme.spacing.xs,
  },
  publishButton: {
    minWidth: 116,
  },
});
