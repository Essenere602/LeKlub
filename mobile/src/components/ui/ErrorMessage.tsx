import { StyleSheet, View } from 'react-native';

import { theme } from '../../config/theme';
import { AppText } from './AppText';

type ErrorMessageProps = {
  message: string | null;
};

export function ErrorMessage({ message }: ErrorMessageProps) {
  if (!message) {
    return null;
  }

  return (
    <View style={styles.container}>
      <AppText style={styles.text}>{message}</AppText>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    backgroundColor: 'rgba(255, 59, 92, 0.12)',
    borderColor: theme.colors.danger,
    borderRadius: theme.radius.md,
    borderWidth: 1,
    padding: theme.spacing.md,
  },
  text: {
    color: theme.colors.danger,
    fontSize: theme.typography.sizes.sm,
  },
});
