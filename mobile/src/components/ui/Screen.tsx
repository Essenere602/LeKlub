import { PropsWithChildren } from 'react';
import { StyleSheet, ViewStyle } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';

import { theme } from '../../config/theme';

type ScreenProps = PropsWithChildren<{
  style?: ViewStyle;
}>;

export function Screen({ children, style }: ScreenProps) {
  return <SafeAreaView style={[styles.container, style]}>{children}</SafeAreaView>;
}

const styles = StyleSheet.create({
  container: {
    backgroundColor: theme.colors.background,
    flex: 1,
    gap: theme.spacing.xl,
    padding: theme.spacing.xl,
  },
});
