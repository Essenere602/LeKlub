import { Pressable, StyleSheet, View } from 'react-native';

import { theme } from '../../config/theme';
import { AppText } from './AppText';

type SettingsRowProps = {
  title: string;
  subtitle?: string;
  meta?: string;
  onPress?: () => void;
};

export function SettingsRow({ meta, onPress, subtitle, title }: SettingsRowProps) {
  const content = (
    <>
      <View style={styles.copy}>
        <AppText style={styles.title}>{title}</AppText>
        {subtitle ? <AppText variant="muted">{subtitle}</AppText> : null}
      </View>
      {meta ? <AppText numberOfLines={2} style={styles.meta}>{meta}</AppText> : null}
      {onPress ? <AppText style={styles.chevron}>›</AppText> : null}
    </>
  );

  if (!onPress) {
    return <View style={styles.row}>{content}</View>;
  }

  return (
    <Pressable
      accessibilityRole="button"
      hitSlop={4}
      onPress={onPress}
      style={({ pressed }) => [styles.row, pressed && styles.pressed]}
    >
      {content}
    </Pressable>
  );
}

const styles = StyleSheet.create({
  row: {
    alignItems: 'center',
    borderBottomColor: theme.colors.border,
    borderBottomWidth: 1,
    flexDirection: 'row',
    gap: theme.spacing.md,
    minHeight: 64,
    paddingVertical: theme.spacing.md,
  },
  copy: {
    flex: 1,
    gap: theme.spacing.xs,
    minWidth: 0,
  },
  title: {
    fontSize: theme.typography.sizes.md,
    fontWeight: theme.typography.weights.semibold,
  },
  meta: {
    color: theme.colors.text.secondary,
    flexShrink: 1,
    fontSize: theme.typography.sizes.sm,
    fontWeight: theme.typography.weights.semibold,
    maxWidth: '45%',
    textAlign: 'right',
  },
  chevron: {
    color: theme.colors.accent,
    fontSize: 28,
    lineHeight: 28,
  },
  pressed: {
    opacity: 0.76,
  },
});
