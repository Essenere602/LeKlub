import { ActivityIndicator, StyleSheet, View } from 'react-native';

import { theme } from '../../config/theme';
import { AppText } from './AppText';

type LoadingStateProps = {
  message?: string;
};

export function LoadingState({ message = 'Chargement...' }: LoadingStateProps) {
  return (
    <View style={styles.container}>
      <ActivityIndicator color={theme.colors.accent} />
      <AppText style={styles.message}>{message}</AppText>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    alignItems: 'center',
    backgroundColor: theme.colors.surface,
    borderColor: theme.colors.border,
    borderRadius: theme.radius.lg,
    borderWidth: 1,
    gap: theme.spacing.md,
    padding: theme.spacing.xl,
  },
  message: {
    color: theme.colors.text.secondary,
    fontSize: theme.typography.sizes.sm,
  },
});
