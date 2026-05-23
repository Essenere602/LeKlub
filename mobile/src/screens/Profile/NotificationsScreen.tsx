import { useCallback, useEffect, useState } from 'react';
import { FlatList, RefreshControl, StyleSheet, View } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { NativeStackScreenProps } from '@react-navigation/native-stack';

import { AppButton } from '../../components/ui/AppButton';
import { AppCard } from '../../components/ui/AppCard';
import { AppHeader } from '../../components/ui/AppHeader';
import { AppText } from '../../components/ui/AppText';
import { EmptyState } from '../../components/ui/EmptyState';
import { ErrorState } from '../../components/ui/ErrorState';
import { LoadingState } from '../../components/ui/LoadingState';
import { Screen } from '../../components/ui/Screen';
import { StatusBadge } from '../../components/ui/StatusBadge';
import { theme } from '../../config/theme';
import { ProfileStackParamList } from '../../navigation/navigation.types';
import { toApiError } from '../../services/api/apiError';
import { notificationService } from '../../services/notification/notificationService';
import { Pagination } from '../../types/feed.types';
import { SystemNotification } from '../../types/notification.types';

type NotificationsScreenProps = NativeStackScreenProps<ProfileStackParamList, 'Notifications'>;

const LIMIT = 10;

export function NotificationsScreen({ navigation }: NotificationsScreenProps) {
  const [notifications, setNotifications] = useState<SystemNotification[]>([]);
  const [pagination, setPagination] = useState<Pagination | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [isRefreshing, setIsRefreshing] = useState(false);
  const [isLoadingMore, setIsLoadingMore] = useState(false);
  const [markingId, setMarkingId] = useState<number | null>(null);

  const loadNotifications = useCallback(async (page = 1, append = false) => {
    const result = await notificationService.listNotifications(page, LIMIT);
    setPagination(result.pagination);
    setNotifications((current) => (append ? [...current, ...result.notifications] : result.notifications));
  }, []);

  const loadInitialNotifications = useCallback(async () => {
    setError(null);
    setIsLoading(true);

    try {
      await loadNotifications();
    } catch (caughtError) {
      setError(toApiError(caughtError).message);
    } finally {
      setIsLoading(false);
    }
  }, [loadNotifications]);

  useEffect(() => {
    void loadInitialNotifications();
  }, [loadInitialNotifications]);

  async function refreshNotifications() {
    setError(null);
    setIsRefreshing(true);

    try {
      await loadNotifications();
    } catch (caughtError) {
      setError(toApiError(caughtError).message);
    } finally {
      setIsRefreshing(false);
    }
  }

  async function loadMoreNotifications() {
    if (!pagination || pagination.page >= pagination.pages || isLoadingMore) {
      return;
    }

    setError(null);
    setIsLoadingMore(true);

    try {
      await loadNotifications(pagination.page + 1, true);
    } catch (caughtError) {
      setError(toApiError(caughtError).message);
    } finally {
      setIsLoadingMore(false);
    }
  }

  async function markAsRead(notificationId: number) {
    setMarkingId(notificationId);
    setError(null);

    try {
      const updatedNotification = await notificationService.markAsRead(notificationId);
      setNotifications((current) => current.map((notification) => (
        notification.id === updatedNotification.id ? updatedNotification : notification
      )));
    } catch (caughtError) {
      setError(toApiError(caughtError).message);
    } finally {
      setMarkingId(null);
    }
  }

  return (
    <Screen style={styles.screen}>
      <FlatList
        contentContainerStyle={styles.content}
        data={notifications}
        keyExtractor={(notification) => String(notification.id)}
        ListEmptyComponent={!isLoading && !error ? (
          <EmptyState message="Les messages système importants apparaîtront ici." title="Aucune notification" />
        ) : null}
        ListFooterComponent={pagination && pagination.page < pagination.pages ? (
          <AppButton label="Charger plus" loading={isLoadingMore} onPress={loadMoreNotifications} variant="secondary" />
        ) : null}
        ListHeaderComponent={
          <View style={styles.header}>
            <AppButton label="Retour au compte" onPress={() => navigation.goBack()} variant="secondary" />
            <AppHeader
              kicker="Compte"
              subtitle="Décisions de modération, avertissements et informations importantes."
              title="Notifications"
            />
            {isLoading ? <LoadingState message="Chargement des notifications..." /> : null}
            {error ? <ErrorState message={error} onRetry={loadInitialNotifications} /> : null}
          </View>
        }
        refreshControl={
          <RefreshControl refreshing={isRefreshing} tintColor={theme.colors.accent} onRefresh={refreshNotifications} />
        }
        renderItem={({ item }) => (
          <NotificationCard
            notification={item}
            marking={markingId === item.id}
            onMarkAsRead={() => markAsRead(item.id)}
          />
        )}
      />
    </Screen>
  );
}

type NotificationCardProps = {
  notification: SystemNotification;
  marking: boolean;
  onMarkAsRead: () => void;
};

function NotificationCard({ marking, notification, onMarkAsRead }: NotificationCardProps) {
  const isUnread = notification.readAt === null;
  const notificationMeta = metaForNotificationType(notification.type);

  return (
    <AppCard style={styles.card} variant={isUnread ? 'accent' : 'default'}>
      <View style={styles.cardHeader}>
        <View style={styles.notificationIdentity}>
          <View style={[styles.notificationIcon, isUnread && styles.notificationIconUnread]}>
            <Ionicons color={isUnread ? theme.colors.text.inverse : theme.colors.accent} name={notificationMeta.icon} size={18} />
          </View>
          <View style={styles.notificationHeaderCopy}>
            <AppText style={styles.typeLabel}>{notificationMeta.label}</AppText>
            <AppText variant="muted">{formatDate(notification.createdAt)}</AppText>
          </View>
        </View>
        <StatusBadge label={isUnread ? 'Non lu' : 'Lu'} variant={isUnread ? 'accent' : 'neutral'} />
      </View>
      <AppText style={styles.title}>{notification.title}</AppText>
      <AppText style={styles.message}>{notification.message}</AppText>
      {isUnread ? (
        <AppButton label="Marquer comme lu" loading={marking} onPress={onMarkAsRead} variant="secondary" />
      ) : null}
    </AppCard>
  );
}

function metaForNotificationType(type: SystemNotification['type']): {
  label: string;
  icon: keyof typeof Ionicons.glyphMap;
} {
  const meta: Record<SystemNotification['type'], { label: string; icon: keyof typeof Ionicons.glyphMap }> = {
    admin_role_granted: { label: 'Rôle admin', icon: 'shield-checkmark-outline' },
    admin_role_removed: { label: 'Rôle admin', icon: 'shield-outline' },
    report_accepted: { label: 'Signalement accepté', icon: 'checkmark-circle-outline' },
    report_rejected: { label: 'Signalement rejeté', icon: 'close-circle-outline' },
    suspension: { label: 'Suspension', icon: 'pause-circle-outline' },
    user_suspended: { label: 'Compte suspendu', icon: 'pause-circle-outline' },
    user_unsuspended: { label: 'Compte réactivé', icon: 'play-circle-outline' },
    warning: { label: 'Avertissement', icon: 'warning-outline' },
  };

  return meta[type];
}

function formatDate(value: string): string {
  const date = new Date(value);

  if (Number.isNaN(date.getTime())) {
    return value;
  }

  return date.toLocaleDateString('fr-FR', {
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
    month: 'short',
  });
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
  card: {
    gap: theme.spacing.md,
  },
  cardHeader: {
    alignItems: 'flex-start',
    flexDirection: 'row',
    gap: theme.spacing.md,
    justifyContent: 'space-between',
  },
  notificationIdentity: {
    alignItems: 'center',
    flex: 1,
    flexDirection: 'row',
    gap: theme.spacing.md,
  },
  notificationIcon: {
    alignItems: 'center',
    backgroundColor: theme.colors.accentSoft,
    borderColor: theme.colors.borderSoft,
    borderRadius: theme.radius.full,
    borderWidth: 1,
    height: 38,
    justifyContent: 'center',
    width: 38,
  },
  notificationIconUnread: {
    backgroundColor: theme.colors.accent,
    borderColor: theme.colors.accent,
  },
  notificationHeaderCopy: {
    flex: 1,
    gap: theme.spacing.xs,
  },
  typeLabel: {
    fontSize: theme.typography.sizes.sm,
    fontWeight: theme.typography.weights.bold,
  },
  title: {
    fontSize: theme.typography.sizes.lg,
    fontWeight: theme.typography.weights.bold,
  },
  message: {
    color: theme.colors.text.secondary,
    fontSize: theme.typography.sizes.md,
    lineHeight: 22,
  },
});
