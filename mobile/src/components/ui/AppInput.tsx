import { StyleSheet, TextInput, TextInputProps, View } from 'react-native';

import { theme } from '../../config/theme';
import { AppText } from './AppText';

type AppInputProps = TextInputProps & {
  label: string;
};

export function AppInput({ label, style, ...props }: AppInputProps) {
  return (
    <View style={styles.container}>
      <AppText variant="label">{label}</AppText>
      <TextInput
        {...props}
        autoCapitalize={props.autoCapitalize ?? 'none'}
        placeholderTextColor={theme.colors.text.muted}
        style={[styles.input, style]}
      />
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    gap: theme.spacing.sm,
  },
  input: {
    backgroundColor: theme.colors.surfaceElevated,
    borderColor: theme.colors.border,
    borderRadius: theme.radius.md,
    borderWidth: 1,
    color: theme.colors.text.primary,
    fontSize: theme.typography.sizes.md,
    minHeight: 48,
    paddingHorizontal: theme.spacing.md,
  },
});
