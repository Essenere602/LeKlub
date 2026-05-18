import { useCallback, useEffect, useState } from 'react';
import { FlatList, RefreshControl, StyleSheet, View } from 'react-native';
import { NativeStackScreenProps } from '@react-navigation/native-stack';

import { AdminModerationCard } from '../../components/admin/AdminModerationCard';
import { AppButton } from '../../components/ui/AppButton';
import { AppHeader } from '../../components/ui/AppHeader';
import { EmptyState } from '../../components/ui/EmptyState';
import { ErrorState } from '../../components/ui/ErrorState';
import { LoadingState } from '../../components/ui/LoadingState';
import { Screen } from '../../components/ui/Screen';
import { theme } from '../../config/theme';
import { AdminStackParamList } from '../../navigation/navigation.types';
import { toApiError } from '../../services/api/apiError';
import { adminService } from '../../services/admin/adminService';
import { AdminComment, AdminPost } from '../../types/admin.types';
import { Pagination } from '../../types/feed.types';

type AdminFeedModerationScreenProps = NativeStackScreenProps<AdminStackParamList, 'AdminFeedModeration'>;
type ModerationTab = 'posts' | 'comments';

const LIMIT = 10;

export function AdminFeedModerationScreen({ navigation }: AdminFeedModerationScreenProps) {
  const [tab, setTab] = useState<ModerationTab>('posts');
  const [posts, setPosts] = useState<AdminPost[]>([]);
  const [comments, setComments] = useState<AdminComment[]>([]);
  const [pagination, setPagination] = useState<Pagination | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [isRefreshing, setIsRefreshing] = useState(false);
  const [isLoadingMore, setIsLoadingMore] = useState(false);
  const [deletingId, setDeletingId] = useState<number | null>(null);

  const loadItems = useCallback(async (page = 1, append = false) => {
    if (tab === 'posts') {
      const result = await adminService.listPosts(page, LIMIT);
      setPagination(result.pagination);
      setPosts((currentPosts) => (append ? [...currentPosts, ...result.posts] : result.posts));
      return;
    }

    const result = await adminService.listComments(page, LIMIT);
    setPagination(result.pagination);
    setComments((currentComments) => (append ? [...currentComments, ...result.comments] : result.comments));
  }, [tab]);

  const loadInitialItems = useCallback(async () => {
    setError(null);
    setIsLoading(true);

    try {
      await loadItems();
    } catch (caughtError) {
      setError(toApiError(caughtError).message);
    } finally {
      setIsLoading(false);
    }
  }, [loadItems]);

  useEffect(() => {
    void loadInitialItems();
  }, [loadInitialItems]);

  async function refreshItems() {
    setError(null);
    setIsRefreshing(true);

    try {
      await loadItems();
    } catch (caughtError) {
      setError(toApiError(caughtError).message);
    } finally {
      setIsRefreshing(false);
    }
  }

  async function loadMoreItems() {
    if (!pagination || pagination.page >= pagination.pages || isLoadingMore) {
      return;
    }

    setError(null);
    setIsLoadingMore(true);

    try {
      await loadItems(pagination.page + 1, true);
    } catch (caughtError) {
      setError(toApiError(caughtError).message);
    } finally {
      setIsLoadingMore(false);
    }
  }

  async function deleteItem(id: number) {
    setDeletingId(id);
    setError(null);

    try {
      if (tab === 'posts') {
        await adminService.deletePost(id);
      } else {
        await adminService.deleteComment(id);
      }

      await loadItems();
    } catch (caughtError) {
      setError(toApiError(caughtError).message);
    } finally {
      setDeletingId(null);
    }
  }

  const data: Array<AdminPost | AdminComment> = tab === 'posts' ? posts : comments;

  return (
    <Screen style={styles.screen}>
      <FlatList<AdminPost | AdminComment>
        contentContainerStyle={styles.content}
        data={data}
        keyExtractor={(item) => String(item.id)}
        ListEmptyComponent={!isLoading && !error ? (
          <EmptyState message="Aucun contenu visible à modérer pour cette section." title="Rien à modérer" />
        ) : null}
        ListFooterComponent={pagination && pagination.page < pagination.pages ? (
          <AppButton label="Charger plus" loading={isLoadingMore} onPress={loadMoreItems} variant="secondary" />
        ) : null}
        ListHeaderComponent={
          <View style={styles.header}>
            <AppButton label="Retour admin" onPress={() => navigation.goBack()} variant="secondary" />
            <AppHeader
              kicker="Administration"
              subtitle="La suppression est logique : le contenu devient invisible côté utilisateur."
              title="Modération Feed"
            />
            <View style={styles.tabs}>
              <TabButton active={tab === 'posts'} label="Posts" onPress={() => setTab('posts')} />
              <TabButton active={tab === 'comments'} label="Commentaires" onPress={() => setTab('comments')} />
            </View>
            {isLoading ? <LoadingState message="Chargement des contenus..." /> : null}
            {error ? <ErrorState message={error} onRetry={loadInitialItems} /> : null}
          </View>
        }
        refreshControl={
          <RefreshControl refreshing={isRefreshing} tintColor={theme.colors.accent} onRefresh={refreshItems} />
        }
        renderItem={({ item }) => (
          <AdminModerationCard
            deleting={deletingId === item.id}
            item={item}
            type={tab === 'posts' ? 'post' : 'comment'}
            onDelete={() => deleteItem(item.id)}
          />
        )}
      />
    </Screen>
  );
}

type TabButtonProps = {
  active: boolean;
  label: string;
  onPress: () => void;
};

function TabButton({ active, label, onPress }: TabButtonProps) {
  return (
    <AppButton
      label={label}
      onPress={onPress}
      variant={active ? 'primary' : 'secondary'}
      style={styles.tabButton}
    />
  );
}

const styles = StyleSheet.create({
  screen: {
    padding: 0,
  },
  content: {
    gap: theme.spacing.md,
    padding: theme.spacing.xl,
  },
  header: {
    gap: theme.spacing.lg,
  },
  tabs: {
    flexDirection: 'row',
    gap: theme.spacing.md,
  },
  tabButton: {
    flex: 1,
  },
});
