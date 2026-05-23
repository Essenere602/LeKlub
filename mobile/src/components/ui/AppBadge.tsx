import { StyleSheet, Text, View } from 'react-native';

import { theme } from '../../config/theme';

type AppBadgeTone = 'accent' | 'neutral' | 'success';

type AppBadgeProps = {
  label: string;
  tone?: AppBadgeTone;
};

export function AppBadge({ label, tone = 'neutral' }: AppBadgeProps) {
  return (
    <View style={[styles.badge, styles[tone]]}>
      <Text numberOfLines={1} style={[styles.label, tone === 'accent' && styles.accentLabel]}>
        {label}
      </Text>
    </View>
  );
}

const styles = StyleSheet.create({
  badge: {
    alignSelf: 'flex-start',
    borderRadius: theme.radius.full,
    borderWidth: 1,
    maxWidth: '100%',
    paddingHorizontal: theme.spacing.md,
    paddingVertical: theme.spacing.xs,
  },
  accent: {
    backgroundColor: theme.colors.accent,
    borderColor: theme.colors.accent,
  },
  neutral: {
    backgroundColor: theme.colors.surfaceStrong,
    borderColor: theme.colors.border,
  },
  success: {
    backgroundColor: 'rgba(0, 255, 136, 0.12)',
    borderColor: theme.colors.success,
  },
  label: {
    color: theme.colors.text.primary,
    flexShrink: 1,
    fontSize: theme.typography.sizes.xs,
    fontWeight: theme.typography.weights.bold,
  },
  accentLabel: {
    color: theme.colors.text.inverse,
  },
});
