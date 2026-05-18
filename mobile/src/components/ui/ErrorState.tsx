import { StyleSheet, View } from 'react-native';
import { Ionicons } from '@expo/vector-icons';

import { theme } from '../../config/theme';
import { AppButton } from './AppButton';
import { AppText } from './AppText';

type ErrorStateProps = {
  message: string;
  title?: string;
  retryLabel?: string;
  onRetry?: () => void;
};

export function ErrorState({ message, onRetry, retryLabel = 'Réessayer', title = 'Une erreur est survenue' }: ErrorStateProps) {
  return (
    <View style={styles.container}>
      <Ionicons color={theme.colors.danger} name="warning-outline" size={28} />
      <AppText style={styles.title}>{title}</AppText>
      <AppText style={styles.message}>{message}</AppText>
      {onRetry ? <AppButton label={retryLabel} onPress={onRetry} variant="secondary" /> : null}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    backgroundColor: 'rgba(255, 59, 92, 0.08)',
    borderColor: theme.colors.danger,
    borderRadius: theme.radius.lg,
    borderWidth: 1,
    gap: theme.spacing.sm,
    padding: theme.spacing.lg,
  },
  title: {
    fontSize: theme.typography.sizes.lg,
    fontWeight: theme.typography.weights.bold,
  },
  message: {
    color: theme.colors.text.secondary,
    fontSize: theme.typography.sizes.sm,
    lineHeight: 20,
  },
});
