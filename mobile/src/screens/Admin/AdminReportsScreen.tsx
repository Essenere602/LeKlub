import { useCallback, useEffect, useState } from 'react';
import { FlatList, RefreshControl, StyleSheet, View } from 'react-native';
import { NativeStackScreenProps } from '@react-navigation/native-stack';

import { AdminReportCard } from '../../components/admin/AdminReportCard';
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
import { AdminReport, AdminReportStatus } from '../../types/admin.types';
import { Pagination } from '../../types/feed.types';

type AdminReportsScreenProps = NativeStackScreenProps<AdminStackParamList, 'AdminReports'>;

const LIMIT = 10;

export function AdminReportsScreen({ navigation }: AdminReportsScreenProps) {
  const [status, setStatus] = useState<AdminReportStatus>('open');
  const [reports, setReports] = useState<AdminReport[]>([]);
  const [pagination, setPagination] = useState<Pagination | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [isRefreshing, setIsRefreshing] = useState(false);
  const [isLoadingMore, setIsLoadingMore] = useState(false);
  const [resolvingId, setResolvingId] = useState<number | null>(null);

  const loadReports = useCallback(async (page = 1, append = false) => {
    const result = await adminService.listReports(page, LIMIT, status);
    setPagination(result.pagination);
    setReports((currentReports) => (append ? [...currentReports, ...result.reports] : result.reports));
  }, [status]);

  const loadInitialReports = useCallback(async () => {
    setError(null);
    setIsLoading(true);

    try {
      await loadReports();
    } catch (caughtError) {
      setError(toApiError(caughtError).message);
    } finally {
      setIsLoading(false);
    }
  }, [loadReports]);

  useEffect(() => {
    void loadInitialReports();
  }, [loadInitialReports]);

  async function refreshReports() {
    setError(null);
    setIsRefreshing(true);

    try {
      await loadReports();
    } catch (caughtError) {
      setError(toApiError(caughtError).message);
    } finally {
      setIsRefreshing(false);
    }
  }

  async function loadMoreReports() {
    if (!pagination || pagination.page >= pagination.pages || isLoadingMore) {
      return;
    }

    setError(null);
    setIsLoadingMore(true);

    try {
      await loadReports(pagination.page + 1, true);
    } catch (caughtError) {
      setError(toApiError(caughtError).message);
    } finally {
      setIsLoadingMore(false);
    }
  }

  async function resolveReport(reportId: number) {
    setResolvingId(reportId);
    setError(null);

    try {
      await adminService.resolveReport(reportId);
      await loadReports();
    } catch (caughtError) {
      setError(toApiError(caughtError).message);
    } finally {
      setResolvingId(null);
    }
  }

  return (
    <Screen style={styles.screen}>
      <FlatList
        contentContainerStyle={styles.content}
        data={reports}
        keyExtractor={(report) => String(report.id)}
        ListEmptyComponent={!isLoading && !error ? (
          <EmptyState
            message="Les signalements apparaîtront ici dès qu'un membre signale un Post ou un Commentaire."
            title="Aucun signalement"
          />
        ) : null}
        ListFooterComponent={pagination && pagination.page < pagination.pages ? (
          <AppButton label="Charger plus" loading={isLoadingMore} onPress={loadMoreReports} variant="secondary" />
        ) : null}
        ListHeaderComponent={
          <View style={styles.header}>
            <AppButton label="Retour admin" onPress={() => navigation.goBack()} variant="secondary" />
            <AppHeader
              kicker="Administration"
              subtitle="Les signalements sont traités séparément des actions de modération."
              title="Signalements"
            />
            <View style={styles.tabs}>
              <TabButton active={status === 'open'} label="Ouverts" onPress={() => setStatus('open')} />
              <TabButton active={status === 'resolved'} label="Résolus" onPress={() => setStatus('resolved')} />
            </View>
            {isLoading ? <LoadingState message="Chargement des signalements..." /> : null}
            {error ? <ErrorState message={error} onRetry={loadInitialReports} /> : null}
          </View>
        }
        refreshControl={
          <RefreshControl refreshing={isRefreshing} tintColor={theme.colors.accent} onRefresh={refreshReports} />
        }
        renderItem={({ item }) => (
          <AdminReportCard
            report={item}
            resolving={resolvingId === item.id}
            onResolve={() => resolveReport(item.id)}
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
