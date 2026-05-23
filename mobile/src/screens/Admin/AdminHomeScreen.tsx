import { useCallback, useEffect, useState } from 'react';
import { ScrollView, StyleSheet, View } from 'react-native';
import { NativeStackScreenProps } from '@react-navigation/native-stack';
import { useBottomTabBarHeight } from '@react-navigation/bottom-tabs';

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
  const tabBarHeight = useBottomTabBarHeight();
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
      <ScrollView
        contentContainerStyle={[styles.content, { paddingBottom: tabBarHeight + theme.spacing['2xl'] }]}
        showsVerticalScrollIndicator={false}
      >
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
            <AppSection title="Priorités modération">
              <View style={styles.statsGrid}>
                <AdminStatCard
                  icon="flag-outline"
                  label="Signalements ouverts"
                  priority="critical"
                  value={overview.openReportsCount}
                />
                <AdminStatCard
                  icon="pause-circle-outline"
                  label="Comptes suspendus"
                  priority="critical"
                  value={overview.suspendedUsersCount}
                />
              </View>
              <View style={styles.statsGrid}>
                <AdminStatCard
                  icon="warning-outline"
                  label="Avertissements"
                  priority="critical"
                  value={overview.warningsCount}
                />
                <AdminStatCard icon="people-outline" label="Utilisateurs" value={overview.usersCount} />
              </View>
            </AppSection>
          ) : null}

          {overview && !isLoading && !error ? (
            <AppSection title="Activité contenu">
              <View style={styles.statsGrid}>
                <AdminStatCard icon="chatbubbles-outline" label="Posts actifs" value={overview.postsCount} />
                <AdminStatCard icon="text-outline" label="Commentaires actifs" value={overview.commentsCount} />
              </View>
              <View style={styles.statsGrid}>
                <AdminStatCard icon="archive-outline" label="Posts supprimés" value={overview.deletedPostsCount} />
                <AdminStatCard icon="file-tray-outline" label="Commentaires supprimés" value={overview.deletedCommentsCount} />
              </View>
              <View style={styles.statsGrid}>
                <AdminStatCard icon="mail-outline" label="Conversations" value={overview.conversationsCount} />
                <AdminStatCard icon="send-outline" label="Messages privés" value={overview.messagesCount} />
              </View>
            </AppSection>
          ) : null}
        </View>

        {MODULES.length === 0 ? <EmptyState title="Aucun module admin" /> : null}
        <AppSection title="Modules admin">
          {MODULES.map((item) => (
            <AdminModuleCard
              key={item.id}
              description={item.description}
              icon={item.icon}
              title={item.title}
              onPress={() => navigation.navigate(item.screen)}
            />
          ))}
        </AppSection>
      </ScrollView>
    </Screen>
  );
}

const styles = StyleSheet.create({
  screen: {
    padding: 0,
  },
  content: {
    gap: theme.spacing.md,
    flexGrow: 1,
    padding: theme.spacing.xl,
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
