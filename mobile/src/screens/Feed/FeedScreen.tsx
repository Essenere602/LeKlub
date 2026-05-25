import { useState } from 'react';
import { Alert, FlatList, RefreshControl, StyleSheet, View } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { NativeStackScreenProps } from '@react-navigation/native-stack';
import { useBottomTabBarHeight } from '@react-navigation/bottom-tabs';

import { UserAvatar } from '../../components/messaging/UserAvatar';
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
import { CreateReportPayload } from '../../types/feed.types';
import { useFeedPosts } from './useFeedPosts';

type FeedScreenProps = NativeStackScreenProps<FeedStackParamList, 'Feed'>;

export function FeedScreen({ navigation }: FeedScreenProps) {
  const tabBarHeight = useBottomTabBarHeight();
  const { user } = useAuth();
  const [content, setContent] = useState('');
  const [reportingPostId, setReportingPostId] = useState<number | null>(null);
  const [isReporting, setIsReporting] = useState(false);
  const [reportError, setReportError] = useState<string | null>(null);
  const feed = useFeedPosts();
  const composerLabel = user?.profile.displayName ?? user?.username ?? 'Klub';
  const composerAvatarUrl = user?.profile.avatarUrl ?? null;

  async function createPost() {
    const created = await feed.createPost(content);

    if (created) {
      setContent('');
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
        data={feed.posts}
        keyExtractor={(post) => String(post.id)}
        ListEmptyComponent={!feed.isLoading ? <EmptyFeed /> : null}
        ListFooterComponent={
          feed.pagination && feed.pagination.page < feed.pagination.pages ? (
            <AppButton
              label="Charger plus"
              loading={feed.isLoadingMore}
              onPress={feed.loadMorePosts}
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
                <UserAvatar label={composerLabel} size={44} uri={composerAvatarUrl} />
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
                <AppButton label="Publier" loading={feed.isCreating} onPress={createPost} style={styles.publishButton} />
              </View>
            </AppCard>

            <ErrorMessage message={feed.error} />
            {feed.isLoading ? <LoadingState message="Chargement du Feed..." /> : null}
          </View>
        }
        refreshControl={
          <RefreshControl
            refreshing={feed.isRefreshing}
            tintColor={theme.colors.accent}
            onRefresh={feed.refreshPosts}
          />
        }
        renderItem={({ item }) => (
          <PostCard
            canManage={item.author.id === user?.id}
            disabled={feed.reactingPostId === item.id}
            onOpen={() => navigation.navigate('PostDetail', { postId: item.id })}
            onReact={(type) => feed.reactToPost(item.id, type)}
            onDelete={() => feed.deletePost(item.id)}
            onReport={() => setReportingPostId(item.id)}
            onRemoveReaction={() => feed.removeReaction(item.id)}
            onUpdate={(updatedContent) => feed.updatePost(item.id, updatedContent)}
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
