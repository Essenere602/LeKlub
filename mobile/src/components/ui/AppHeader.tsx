import { ReactNode } from 'react';
import { StyleSheet, View, ViewStyle } from 'react-native';

import { theme } from '../../config/theme';
import { AppText } from './AppText';

type AppHeaderProps = {
  title: string;
  subtitle?: string;
  kicker?: string;
  right?: ReactNode;
  style?: ViewStyle;
};

export function AppHeader({ kicker, right, style, subtitle, title }: AppHeaderProps) {
  return (
    <View style={[styles.container, style]}>
      <View style={styles.copy}>
        {kicker ? <AppText style={styles.kicker}>{kicker}</AppText> : null}
        <AppText variant="title">{title}</AppText>
        {subtitle ? <AppText variant="subtitle">{subtitle}</AppText> : null}
      </View>
      {right ? <View style={styles.right}>{right}</View> : null}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    gap: theme.spacing.md,
  },
  copy: {
    flex: 1,
    gap: theme.spacing.sm,
  },
  kicker: {
    color: theme.colors.accent,
    fontSize: theme.typography.sizes.sm,
    fontWeight: theme.typography.weights.bold,
    textTransform: 'uppercase',
  },
  right: {
    alignItems: 'flex-start',
  },
});
