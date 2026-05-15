import { PropsWithChildren } from 'react';
import { StyleSheet, Text, TextProps } from 'react-native';

import { theme } from '../../config/theme';

type AppTextVariant = 'title' | 'subtitle' | 'body' | 'label' | 'muted';

type AppTextProps = PropsWithChildren<TextProps & {
  variant?: AppTextVariant;
}>;

export function AppText({ children, variant = 'body', style, ...props }: AppTextProps) {
  return (
    <Text {...props} style={[styles.base, styles[variant], style]}>
      {children}
    </Text>
  );
}

const styles = StyleSheet.create({
  base: {
    color: theme.colors.text.primary,
  },
  title: {
    fontSize: theme.typography.sizes['2xl'],
    fontWeight: theme.typography.weights.bold,
  },
  subtitle: {
    color: theme.colors.text.secondary,
    fontSize: theme.typography.sizes.md,
    lineHeight: 24,
  },
  body: {
    fontSize: theme.typography.sizes.md,
    lineHeight: 22,
  },
  label: {
    color: theme.colors.text.secondary,
    fontSize: theme.typography.sizes.sm,
    fontWeight: theme.typography.weights.semibold,
  },
  muted: {
    color: theme.colors.text.muted,
    fontSize: theme.typography.sizes.sm,
  },
});
