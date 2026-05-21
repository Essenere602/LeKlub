import { useCallback, useEffect, useState } from 'react';
import { FlatList, RefreshControl, StyleSheet, View } from 'react-native';
import { NativeStackScreenProps } from '@react-navigation/native-stack';

import { AdminWarningCard } from '../../components/admin/AdminWarningCard';
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
import { AdminWarning } from '../../types/admin.types';
import { Pagination } from '../../types/feed.types';

type AdminWarningsScreenProps = NativeStackScreenProps<AdminStackParamList, 'AdminWarnings'>;

const LIMIT = 10;

export function AdminWarningsScreen({ navigation }: AdminWarningsScreenProps) {
  const [warnings, setWarnings] = useState<AdminWarning[]>([]);
  const [pagination, setPagination] = useState<Pagination | null>(null);
  const [suspendedOnly, setSuspendedOnly] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [isRefreshing, setIsRefreshing] = useState(false);
  const [isLoadingMore, setIsLoadingMore] = useState(false);

  const loadWarnings = useCallback(async (page = 1, append = false) => {
    const result = await adminService.listWarnings(page, LIMIT, undefined, suspendedOnly);
    setPagination(result.pagination);
    setWarnings((currentWarnings) => (append ? [...currentWarnings, ...result.warnings] : result.warnings));
  }, [suspendedOnly]);

  const loadInitialWarnings = useCallback(async () => {
    setError(null);
    setIsLoading(true);

    try {
      await loadWarnings();
    } catch (caughtError) {
      setError(toApiError(caughtError).message);
    } finally {
      setIsLoading(false);
    }
  }, [loadWarnings]);

  useEffect(() => {
    void loadInitialWarnings();
  }, [loadInitialWarnings]);

  async function refreshWarnings() {
    setError(null);
    setIsRefreshing(true);

    try {
      await loadWarnings();
    } catch (caughtError) {
      setError(toApiError(caughtError).message);
    } finally {
      setIsRefreshing(false);
    }
  }

  async function loadMoreWarnings() {
    if (!pagination || pagination.page >= pagination.pages || isLoadingMore) {
      return;
    }

    setError(null);
    setIsLoadingMore(true);

    try {
      await loadWarnings(pagination.page + 1, true);
    } catch (caughtError) {
      setError(toApiError(caughtError).message);
    } finally {
      setIsLoadingMore(false);
    }
  }

  return (
    <Screen style={styles.screen}>
      <FlatList
        contentContainerStyle={styles.content}
        data={warnings}
        keyExtractor={(warning) => String(warning.id)}
        ListEmptyComponent={!isLoading && !error ? (
          <EmptyState
            message={
              suspendedOnly
                ? 'Aucun utilisateur suspendu temporairement pour le moment.'
                : 'Les avertissements apparaîtront ici lorsqu’un contenu signalé est supprimé.'
            }
            title="Aucun avertissement"
          />
        ) : null}
        ListFooterComponent={pagination && pagination.page < pagination.pages ? (
          <AppButton label="Charger plus" loading={isLoadingMore} onPress={loadMoreWarnings} variant="secondary" />
        ) : null}
        ListHeaderComponent={
          <View style={styles.header}>
            <AppButton label="Retour admin" onPress={() => navigation.goBack()} variant="secondary" />
            <AppHeader
              kicker="Administration"
              subtitle="Suivi des avertissements et suspensions temporaires liés aux signalements."
              title="Avertissements"
            />
            <View style={styles.tabs}>
              <AppButton
                label="Tous"
                onPress={() => setSuspendedOnly(false)}
                style={styles.tabButton}
                variant={!suspendedOnly ? 'primary' : 'secondary'}
              />
              <AppButton
                label="Suspendus"
                onPress={() => setSuspendedOnly(true)}
                style={styles.tabButton}
                variant={suspendedOnly ? 'primary' : 'secondary'}
              />
            </View>
            {isLoading ? <LoadingState message="Chargement des avertissements..." /> : null}
            {error ? <ErrorState message={error} onRetry={loadInitialWarnings} /> : null}
          </View>
        }
        refreshControl={
          <RefreshControl refreshing={isRefreshing} tintColor={theme.colors.accent} onRefresh={refreshWarnings} />
        }
        renderItem={({ item }) => <AdminWarningCard warning={item} />}
      />
    </Screen>
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
