import { StyleSheet, View } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { ComponentProps } from 'react';

import { theme } from '../../config/theme';
import { AppText } from '../ui/AppText';

type FootballBadgeTone = 'accent' | 'neutral' | 'success' | 'warning' | 'danger';

type FootballBadgeProps = {
  label: string;
  icon?: ComponentProps<typeof Ionicons>['name'];
  tone?: FootballBadgeTone;
};

export function FootballBadge({ icon, label, tone = 'neutral' }: FootballBadgeProps) {
  const color = colorByTone[tone];

  return (
    <View style={[styles.badge, { borderColor: color, backgroundColor: backgroundByTone[tone] }]}>
      {icon ? <Ionicons color={tone === 'accent' ? theme.colors.football.blackPure : color} name={icon} size={14} /> : null}
      <AppText style={[styles.label, { color: tone === 'accent' ? theme.colors.football.blackPure : color }]}>{label}</AppText>
    </View>
  );
}

const colorByTone: Record<FootballBadgeTone, string> = {
  accent: theme.colors.football.neon,
  danger: theme.colors.danger,
  neutral: theme.colors.text.secondary,
  success: theme.colors.success,
  warning: theme.colors.warning,
};

const backgroundByTone: Record<FootballBadgeTone, string> = {
  accent: theme.colors.football.neon,
  danger: 'rgba(255, 59, 92, 0.16)',
  neutral: theme.colors.football.card,
  success: 'rgba(57, 255, 20, 0.12)',
  warning: 'rgba(255, 204, 0, 0.12)',
};

const styles = StyleSheet.create({
  badge: {
    alignItems: 'center',
    borderRadius: theme.radius.sm,
    borderWidth: 1,
    flexDirection: 'row',
    gap: theme.spacing.xs,
    paddingHorizontal: theme.spacing.sm,
    paddingVertical: theme.spacing.xs,
  },
  label: {
    fontSize: theme.typography.sizes.xs,
    fontWeight: theme.typography.weights.bold,
    textTransform: 'uppercase',
  },
});
