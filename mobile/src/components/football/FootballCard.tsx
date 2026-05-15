import { PropsWithChildren } from 'react';
import { StyleProp, StyleSheet, View, ViewStyle } from 'react-native';

import { theme } from '../../config/theme';

type FootballCardVariant = 'default' | 'accent' | 'table';

type FootballCardProps = PropsWithChildren<{
  style?: StyleProp<ViewStyle>;
  variant?: FootballCardVariant;
}>;

export function FootballCard({ children, style, variant = 'default' }: FootballCardProps) {
  return <View style={[styles.card, styles[variant], style]}>{children}</View>;
}

const styles = StyleSheet.create({
  card: {
    backgroundColor: theme.colors.football.card,
    borderColor: theme.colors.football.border,
    borderRadius: theme.radius.lg,
    borderWidth: 2,
    padding: theme.spacing.lg,
  },
  default: {},
  accent: {
    borderColor: theme.colors.football.neon,
  },
  table: {
    backgroundColor: theme.colors.football.surface,
    overflow: 'hidden',
    padding: 0,
  },
});
