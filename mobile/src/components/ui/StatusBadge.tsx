import { StyleSheet, Text, View } from 'react-native';

import { theme } from '../../config/theme';

type StatusBadgeVariant = 'accent' | 'success' | 'warning' | 'danger' | 'neutral';

type StatusBadgeProps = {
  label: string;
  variant?: StatusBadgeVariant;
};

export function StatusBadge({ label, variant = 'neutral' }: StatusBadgeProps) {
  return (
    <View style={[styles.badge, styles[variant]]}>
      <Text style={[styles.label, variant === 'accent' && styles.inverseLabel]}>{label}</Text>
    </View>
  );
}

const styles = StyleSheet.create({
  badge: {
    alignSelf: 'flex-start',
    borderRadius: 999,
    borderWidth: 1,
    paddingHorizontal: theme.spacing.md,
    paddingVertical: theme.spacing.xs,
  },
  accent: {
    backgroundColor: theme.colors.accent,
    borderColor: theme.colors.accent,
  },
  success: {
    backgroundColor: 'rgba(57, 255, 20, 0.12)',
    borderColor: theme.colors.success,
  },
  warning: {
    backgroundColor: 'rgba(255, 204, 0, 0.12)',
    borderColor: theme.colors.warning,
  },
  danger: {
    backgroundColor: 'rgba(255, 59, 92, 0.12)',
    borderColor: theme.colors.danger,
  },
  neutral: {
    backgroundColor: theme.colors.surfaceElevated,
    borderColor: theme.colors.border,
  },
  label: {
    color: theme.colors.text.primary,
    fontSize: theme.typography.sizes.xs,
    fontWeight: theme.typography.weights.bold,
    textTransform: 'uppercase',
  },
  inverseLabel: {
    color: theme.colors.text.inverse,
  },
});
