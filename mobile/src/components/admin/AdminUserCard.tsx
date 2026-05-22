import { Pressable, StyleSheet, View } from 'react-native';
import { Ionicons } from '@expo/vector-icons';

import { theme } from '../../config/theme';
import { AdminUser } from '../../types/admin.types';
import { AppCard } from '../ui/AppCard';
import { AppText } from '../ui/AppText';
import { StatusBadge } from '../ui/StatusBadge';

type AdminUserCardProps = {
  user: AdminUser;
  loading?: boolean;
  onSuspend: () => void;
  onUnsuspend: () => void;
};

export function AdminUserCard({ loading = false, onSuspend, onUnsuspend, user }: AdminUserCardProps) {
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
          <StatusBadge
            label={user.isSuspended ? 'Suspendu' : 'Actif'}
            variant={user.isSuspended ? 'danger' : 'success'}
          />
        </View>
        <AppText>{displayName}</AppText>
        <AppText variant="muted">Créé le {formatDate(user.createdAt)}</AppText>
        {user.isSuspended && user.suspendedUntil ? (
          <AppText variant="muted">Suspendu jusqu'au {formatDate(user.suspendedUntil)}</AppText>
        ) : null}
        {!isAdmin ? (
          <View style={styles.actions}>
            {user.isSuspended ? (
              <ActionButton
                disabled={loading}
                icon="play-circle-outline"
                label="Lever suspension"
                onPress={onUnsuspend}
                variant="success"
              />
            ) : (
              <ActionButton
                disabled={loading}
                icon="pause-circle-outline"
                label="Suspendre"
                onPress={onSuspend}
                variant="danger"
              />
            )}
          </View>
        ) : (
          <AppText variant="muted">Suspension manuelle admin désactivée dans ce MVP.</AppText>
        )}
      </View>
    </AppCard>
  );
}

type ActionButtonProps = {
  disabled: boolean;
  icon: keyof typeof Ionicons.glyphMap;
  label: string;
  onPress: () => void;
  variant: 'danger' | 'success';
};

function ActionButton({ disabled, icon, label, onPress, variant }: ActionButtonProps) {
  return (
    <Pressable
      accessibilityRole="button"
      disabled={disabled}
      onPress={onPress}
      style={({ pressed }) => [
        styles.actionButton,
        variant === 'danger' ? styles.dangerButton : styles.successButton,
        pressed && styles.pressed,
        disabled && styles.disabled,
      ]}
    >
      <Ionicons color={variant === 'danger' ? theme.colors.danger : theme.colors.success} name={icon} size={17} />
      <AppText style={styles.actionLabel}>{label}</AppText>
    </Pressable>
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
  actions: {
    alignItems: 'flex-start',
    marginTop: theme.spacing.sm,
  },
  actionButton: {
    alignItems: 'center',
    borderRadius: theme.radius.md,
    borderWidth: 1,
    flexDirection: 'row',
    gap: theme.spacing.sm,
    minHeight: 40,
    paddingHorizontal: theme.spacing.md,
  },
  dangerButton: {
    backgroundColor: 'rgba(255, 59, 92, 0.1)',
    borderColor: theme.colors.danger,
  },
  successButton: {
    backgroundColor: 'rgba(57, 255, 20, 0.1)',
    borderColor: theme.colors.success,
  },
  actionLabel: {
    fontSize: theme.typography.sizes.sm,
    fontWeight: theme.typography.weights.bold,
  },
  pressed: {
    opacity: 0.76,
  },
  disabled: {
    opacity: 0.45,
  },
});
