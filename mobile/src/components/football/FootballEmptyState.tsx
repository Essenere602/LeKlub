import { ComponentProps } from 'react';
import { ActivityIndicator, StyleSheet, View } from 'react-native';
import { Ionicons } from '@expo/vector-icons';

import { theme } from '../../config/theme';
import { AppButton } from '../ui/AppButton';
import { AppText } from '../ui/AppText';

type FootballEmptyStateProps = {
  icon?: ComponentProps<typeof Ionicons>['name'];
  message: string;
  onRetry?: () => void;
  title: string;
  type?: 'empty' | 'error' | 'loading';
};

export function FootballEmptyState({
  icon = 'football-outline',
  message,
  onRetry,
  title,
  type = 'empty',
}: FootballEmptyStateProps) {
  const isLoading = type === 'loading';
  const color = type === 'error' ? theme.colors.danger : theme.colors.football.neon;

  return (
    <View style={styles.container}>
      {isLoading ? (
        <ActivityIndicator color={theme.colors.football.neon} size="large" />
      ) : (
        <Ionicons color={color} name={icon} size={64} />
      )}
      <AppText style={styles.title}>{title}</AppText>
      <AppText style={styles.message}>{message}</AppText>
      {onRetry ? <AppButton label="Réessayer" onPress={onRetry} variant="secondary" /> : null}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    alignItems: 'center',
    gap: theme.spacing.md,
    paddingHorizontal: theme.spacing.xl,
    paddingVertical: 40,
  },
  title: {
    color: theme.colors.text.secondary,
    fontSize: theme.typography.sizes.lg,
    fontWeight: theme.typography.weights.semibold,
    textAlign: 'center',
  },
  message: {
    color: theme.colors.text.muted,
    fontSize: theme.typography.sizes.md,
    lineHeight: 22,
    textAlign: 'center',
  },
});
