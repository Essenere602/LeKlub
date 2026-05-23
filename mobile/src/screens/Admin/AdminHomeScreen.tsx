import { useCallback, useEffect, useState } from 'react';
import { FlatList, StyleSheet, View } from 'react-native';
import { NativeStackScreenProps } from '@react-navigation/native-stack';

import { AdminModuleCard } from '../../components/admin/AdminModuleCard';
import { AdminStatCard } from '../../components/admin/AdminStatCard';
import { AppBadge } from '../../components/ui/AppBadge';
import { AppHeader } from '../../components/ui/AppHeader';
import { AppSection } from '../../components/ui/AppSection';
import { EmptyState } from '../../components/ui/EmptyState';
import { ErrorState } from '../../components/ui/ErrorState';
import { LoadingState } from '../../components/ui/LoadingState';
import { Screen } from '../../components/ui/Screen';
import { theme } from '../../config/theme';
import { AdminStackParamList } from '../../navigation/navigation.types';
import { toApiError } from '../../services/api/apiError';
import { adminService } from '../../services/admin/adminService';
import { AdminOverview } from '../../types/admin.types';

type AdminHomeScreenProps = NativeStackScreenProps<AdminStackParamList, 'AdminHome'>;

type AdminModule = {
  id: string;
  title: string;
  description: string;
  icon: 'people-outline' | 'shield-checkmark-outline' | 'flag-outline' | 'warning-outline';
  screen: keyof AdminStackParamList;
};

const MODULES: AdminModule[] = [
  {
    id: 'users',
    title: 'Utilisateurs',
    description: 'Consulter les comptes sans exposer de données sensibles.',
    icon: 'people-outline',
    screen: 'AdminUsers',
  },
  {
    id: 'moderation',
    title: 'Modération Feed',
    description: 'Supprimer logiquement Posts et Commentaires.',
    icon: 'shield-checkmark-outline',
    screen: 'AdminFeedModeration',
  },
  {
    id: 'reports',
    title: 'Signalements',
    description: 'Consulter et résoudre les signalements utilisateurs.',
    icon: 'flag-outline',
    screen: 'AdminReports',
  },
  {
    id: 'warnings',
    title: 'Avertissements',
    description: 'Suivre les avertissements et suspensions temporaires.',
    icon: 'warning-outline',
    screen: 'AdminWarnings',
  },
];

export function AdminHomeScreen({ navigation }: AdminHomeScreenProps) {
  const [overview, setOverview] = useState<AdminOverview | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  const loadOverview = useCallback(async () => {
    setError(null);
    setIsLoading(true);

    try {
      setOverview(await adminService.getOverview());
    } catch (caughtError) {
      setError(toApiError(caughtError).message);
    } finally {
      setIsLoading(false);
    }
  }, []);

  useEffect(() => {
    void loadOverview();
  }, [loadOverview]);

  return (
    <Screen style={styles.screen}>
      <FlatList
        contentContainerStyle={styles.content}
        data={MODULES}
        keyExtractor={(module) => module.id}
        ListEmptyComponent={<EmptyState title="Aucun module admin" />}
        ListHeaderComponent={
          <View style={styles.header}>
            <AppHeader
              kicker="Back office"
              subtitle="Supervision simple et modération du MVP LeKlub."
              title="Administration"
              right={<AppBadge label="Accès admin" tone="accent" />}
            />

            {isLoading ? <LoadingState message="Chargement de la synthèse admin..." /> : null}
            {error ? <ErrorState message={error} onRetry={loadOverview} title="Admin indisponible" /> : null}

            {overview && !isLoading && !error ? (
              <AppSection title="Synthèse">
                <View style={styles.statsGrid}>
                  <AdminStatCard icon="people-outline" label="Utilisateurs" value={overview.usersCount} />
                  <AdminStatCard icon="pause-circle-outline" label="Suspendus" value={overview.suspendedUsersCount} />
                </View>
                <View style={styles.statsGrid}>
                  <AdminStatCard icon="chatbubbles-outline" label="Posts actifs" value={overview.postsCount} />
                  <AdminStatCard icon="archive-outline" label="Posts supprimés" value={overview.deletedPostsCount} />
                </View>
                <View style={styles.statsGrid}>
                  <AdminStatCard icon="text-outline" label="Commentaires actifs" value={overview.commentsCount} />
                  <AdminStatCard icon="file-tray-outline" label="Commentaires supprimés" value={overview.deletedCommentsCount} />
                </View>
                <View style={styles.statsGrid}>
                  <AdminStatCard icon="flag-outline" label="Signalements ouverts" value={overview.openReportsCount} />
                  <AdminStatCard icon="warning-outline" label="Avertissements" value={overview.warningsCount} />
                </View>
                <View style={styles.statsGrid}>
                  <AdminStatCard icon="mail-outline" label="Conversations" value={overview.conversationsCount} />
                  <AdminStatCard icon="send-outline" label="Messages privés" value={overview.messagesCount} />
                </View>
              </AppSection>
            ) : null}
          </View>
        }
        renderItem={({ item }) => (
          <AdminModuleCard
            description={item.description}
            icon={item.icon}
            title={item.title}
            onPress={() => navigation.navigate(item.screen)}
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
    gap: theme.spacing.md,
    padding: theme.spacing.xl,
    paddingBottom: 120,
  },
  header: {
    gap: theme.spacing.lg,
  },
  statsGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: theme.spacing.md,
  },
});
