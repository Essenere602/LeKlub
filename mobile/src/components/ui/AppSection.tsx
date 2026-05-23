import { PropsWithChildren } from 'react';
import { StyleSheet, View } from 'react-native';

import { theme } from '../../config/theme';
import { AppCard } from './AppCard';
import { AppText } from './AppText';

type AppSectionProps = PropsWithChildren<{
  title: string;
  subtitle?: string;
}>;

export function AppSection({ children, subtitle, title }: AppSectionProps) {
  return (
    <AppCard style={styles.card}>
      <View style={styles.header}>
        <AppText style={styles.title}>{title}</AppText>
        {subtitle ? <AppText style={styles.subtitle}>{subtitle}</AppText> : null}
      </View>
      <View style={styles.content}>{children}</View>
    </AppCard>
  );
}

const styles = StyleSheet.create({
  card: {
    paddingBottom: 0,
  },
  header: {
    gap: theme.spacing.xs,
    marginBottom: theme.spacing.sm,
  },
  title: {
    color: theme.colors.accent,
    fontSize: theme.typography.sizes.sm,
    fontWeight: theme.typography.weights.bold,
  },
  subtitle: {
    color: theme.colors.text.muted,
    fontSize: theme.typography.sizes.sm,
    lineHeight: 20,
  },
  content: {
    gap: theme.spacing.md,
  },
});
