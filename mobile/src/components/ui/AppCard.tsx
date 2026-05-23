import { PropsWithChildren } from 'react';
import { StyleSheet, View, ViewStyle } from 'react-native';

import { theme } from '../../config/theme';

type AppCardVariant = 'default' | 'elevated' | 'accent';

type AppCardProps = PropsWithChildren<{
  style?: ViewStyle;
  variant?: AppCardVariant;
}>;

export function AppCard({ children, style, variant = 'default' }: AppCardProps) {
  return <View style={[styles.base, styles[variant], style]}>{children}</View>;
}

const styles = StyleSheet.create({
  base: {
    backgroundColor: theme.colors.surface,
    borderColor: theme.colors.borderSoft,
    borderRadius: theme.radius.lg,
    borderWidth: 1,
    padding: theme.spacing.lg,
  },
  default: {},
  elevated: {
    backgroundColor: theme.colors.surfaceStrong,
    borderColor: theme.colors.border,
  },
  accent: {
    backgroundColor: theme.colors.surfaceElevated,
    borderColor: theme.colors.accentGlow,
  },
});
