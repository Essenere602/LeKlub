import { StyleSheet, View } from 'react-native';

import { theme } from '../../config/theme';
import { AdminUser } from '../../types/admin.types';
import { AppCard } from '../ui/AppCard';
import { AppText } from '../ui/AppText';
import { StatusBadge } from '../ui/StatusBadge';

type AdminUserCardProps = {
  user: AdminUser;
};

export function AdminUserCard({ user }: AdminUserCardProps) {
  const displayName = user.displayName ?? user.username;
  const isAdmin = user.roles.includes('ROLE_ADMIN');

  return (
    <AppCard style={styles.card}>
      <View style={styles.avatar}>
        <AppText style={styles.avatarText}>{displayName.slice(0, 1).toUpperCase()}</AppText>
      </View>
      <View style={styles.copy}>
        <View style={styles.titleRow}>
          <AppText variant="label">@{user.username}</AppText>
          {isAdmin ? <StatusBadge label="Admin" variant="accent" /> : <StatusBadge label="User" variant="neutral" />}
        </View>
        <AppText>{displayName}</AppText>
        <AppText variant="muted">Créé le {formatDate(user.createdAt)}</AppText>
      </View>
    </AppCard>
  );
}

function formatDate(value: string): string {
  const date = new Date(value);

  if (Number.isNaN(date.getTime())) {
    return value;
  }

  return date.toLocaleDateString('fr-FR');
}

const styles = StyleSheet.create({
  card: {
    alignItems: 'center',
    flexDirection: 'row',
    gap: theme.spacing.md,
  },
  avatar: {
    alignItems: 'center',
    backgroundColor: theme.colors.accentSoft,
    borderColor: theme.colors.accent,
    borderRadius: 22,
    borderWidth: 1,
    height: 44,
    justifyContent: 'center',
    width: 44,
  },
  avatarText: {
    color: theme.colors.accent,
    fontWeight: theme.typography.weights.bold,
  },
  copy: {
    flex: 1,
    gap: theme.spacing.xs,
  },
  titleRow: {
    alignItems: 'center',
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: theme.spacing.sm,
  },
});
