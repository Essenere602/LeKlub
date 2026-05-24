import { Pressable, StyleSheet, View } from 'react-native';
import { Ionicons } from '@expo/vector-icons';

import { theme } from '../../config/theme';
import { AdminUser } from '../../types/admin.types';
import { formatShortDate } from '../../utils/dateFormat';
import { AppCard } from '../ui/AppCard';
import { AppText } from '../ui/AppText';
import { StatusBadge } from '../ui/StatusBadge';
import { UserAvatar } from '../messaging/UserAvatar';

type AdminUserCardProps = {
  user: AdminUser;
  loading?: boolean;
  currentUserId?: number;
  onDemoteAdmin: () => void;
  onPromoteAdmin: () => void;
  onSuspend: () => void;
  onUnsuspend: () => void;
};

export function AdminUserCard({
  currentUserId,
  loading = false,
  onDemoteAdmin,
  onPromoteAdmin,
  onSuspend,
  onUnsuspend,
  user,
}: AdminUserCardProps) {
  const displayName = user.displayName ?? user.username;
  const isAdmin = user.roles.includes('ROLE_ADMIN');
  const isCurrentUser = currentUserId === user.id;

  return (
    <AppCard style={styles.card}>
      <View style={styles.copy}>
        <View style={styles.identityRow}>
          <UserAvatar label={displayName} size={44} uri={user.avatarUrl} />
          <View style={styles.identity}>
            <AppText style={styles.displayName}>{displayName}</AppText>
            <AppText variant="muted">@{user.username} · créé le {formatShortDate(user.createdAt)}</AppText>
          </View>
        </View>

        <View style={styles.statusRow}>
          {isAdmin ? <StatusBadge label="ADMIN" variant="accent" /> : <StatusBadge label="USER" variant="neutral" />}
          <StatusBadge
            label={user.isSuspended ? 'Suspendu' : 'Actif'}
            variant={user.isSuspended ? 'danger' : 'success'}
          />
        </View>

        {user.isSuspended && user.suspendedUntil ? (
          <View style={styles.suspensionBox}>
            <Ionicons color={theme.colors.danger} name="pause-circle-outline" size={18} />
            <AppText style={styles.suspensionText}>Suspendu jusqu'au {formatShortDate(user.suspendedUntil)}</AppText>
          </View>
        ) : null}
        {isCurrentUser ? (
          <View style={styles.infoBox}>
            <Ionicons color={theme.colors.text.muted} name="lock-closed-outline" size={16} />
            <AppText variant="muted">Vos propres rôles ne sont pas modifiables.</AppText>
          </View>
        ) : (
          <View style={styles.actions}>
            {isAdmin ? (
              <ActionButton
                disabled={loading}
                icon="shield-outline"
                label="Retirer admin"
                onPress={onDemoteAdmin}
                variant="warning"
              />
            ) : null}
            {!isAdmin && !user.isSuspended ? (
              <>
                <ActionButton
                  disabled={loading}
                  icon="shield-checkmark-outline"
                  label="Promouvoir admin"
                  onPress={onPromoteAdmin}
                  variant="accent"
                />
                <ActionButton
                  disabled={loading}
                  icon="pause-circle-outline"
                  label="Suspendre"
                  onPress={onSuspend}
                  variant="danger"
                />
              </>
            ) : null}
            {!isAdmin && user.isSuspended ? (
              <ActionButton
                disabled={loading}
                icon="play-circle-outline"
                label="Lever suspension"
                onPress={onUnsuspend}
                variant="success"
              />
            ) : null}
          </View>
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
  variant: 'accent' | 'danger' | 'success' | 'warning';
};

function ActionButton({ disabled, icon, label, onPress, variant }: ActionButtonProps) {
  const iconColor = {
    accent: theme.colors.accent,
    danger: theme.colors.danger,
    success: theme.colors.success,
    warning: theme.colors.warning,
  }[variant];

  return (
    <Pressable
      accessibilityRole="button"
      disabled={disabled}
      onPress={onPress}
      style={({ pressed }) => [
        styles.actionButton,
        variant === 'accent' && styles.accentButton,
        variant === 'danger' && styles.dangerButton,
        variant === 'success' && styles.successButton,
        variant === 'warning' && styles.warningButton,
        pressed && styles.pressed,
        disabled && styles.disabled,
      ]}
    >
      <Ionicons color={iconColor} name={icon} size={17} />
      <AppText style={styles.actionLabel}>{label}</AppText>
    </Pressable>
  );
}

const styles = StyleSheet.create({
  card: {
    gap: theme.spacing.md,
  },
  copy: {
    gap: theme.spacing.md,
  },
  identityRow: {
    alignItems: 'center',
    flexDirection: 'row',
    gap: theme.spacing.md,
  },
  identity: {
    flex: 1,
    gap: theme.spacing.xs,
  },
  displayName: {
    fontSize: theme.typography.sizes.lg,
    fontWeight: theme.typography.weights.bold,
  },
  statusRow: {
    alignItems: 'center',
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: theme.spacing.sm,
  },
  suspensionBox: {
    alignItems: 'center',
    backgroundColor: 'rgba(255, 59, 92, 0.1)',
    borderColor: theme.colors.danger,
    borderRadius: theme.radius.md,
    borderWidth: 1,
    flexDirection: 'row',
    gap: theme.spacing.sm,
    padding: theme.spacing.md,
  },
  suspensionText: {
    color: theme.colors.text.secondary,
    flex: 1,
    fontSize: theme.typography.sizes.sm,
    lineHeight: 20,
  },
  infoBox: {
    alignItems: 'center',
    backgroundColor: theme.colors.surfaceElevated,
    borderColor: theme.colors.borderSoft,
    borderRadius: theme.radius.md,
    borderWidth: 1,
    flexDirection: 'row',
    gap: theme.spacing.sm,
    padding: theme.spacing.md,
  },
  actions: {
    alignItems: 'flex-start',
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: theme.spacing.sm,
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
  accentButton: {
    backgroundColor: theme.colors.accentSoft,
    borderColor: theme.colors.accent,
  },
  successButton: {
    backgroundColor: 'rgba(57, 255, 20, 0.1)',
    borderColor: theme.colors.success,
  },
  warningButton: {
    backgroundColor: 'rgba(255, 184, 77, 0.12)',
    borderColor: theme.colors.warning,
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
