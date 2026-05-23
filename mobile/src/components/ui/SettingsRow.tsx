import { Ionicons } from '@expo/vector-icons';
import { Pressable, StyleSheet, View } from 'react-native';

import { theme } from '../../config/theme';
import { AppText } from './AppText';

type SettingsRowProps = {
  title: string;
  subtitle?: string;
  meta?: string;
  icon?: keyof typeof Ionicons.glyphMap;
  onPress?: () => void;
};

export function SettingsRow({ icon, meta, onPress, subtitle, title }: SettingsRowProps) {
  const content = (
    <>
      {icon ? (
        <View style={styles.icon}>
          <Ionicons color={theme.colors.accent} name={icon} size={20} />
        </View>
      ) : null}
      <View style={styles.copy}>
        <AppText style={styles.title}>{title}</AppText>
        {subtitle ? <AppText variant="muted">{subtitle}</AppText> : null}
      </View>
      {meta ? <AppText style={styles.meta}>{meta}</AppText> : null}
      {onPress ? <AppText style={styles.chevron}>›</AppText> : null}
    </>
  );

  if (!onPress) {
    return <View style={styles.row}>{content}</View>;
  }

  return (
    <Pressable
      accessibilityRole="button"
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
    backgroundColor: theme.colors.surfaceElevated,
    borderColor: theme.colors.borderSoft,
    borderRadius: theme.radius.md,
    borderWidth: 1,
    flexDirection: 'row',
    gap: theme.spacing.md,
    minHeight: 68,
    padding: theme.spacing.md,
  },
  icon: {
    alignItems: 'center',
    backgroundColor: theme.colors.accentSoft,
    borderRadius: theme.radius.md,
    height: 40,
    justifyContent: 'center',
    width: 40,
  },
  copy: {
    flex: 1,
    gap: theme.spacing.xs,
  },
  title: {
    fontSize: theme.typography.sizes.md,
    fontWeight: theme.typography.weights.semibold,
  },
  meta: {
    color: theme.colors.text.secondary,
    fontSize: theme.typography.sizes.sm,
    fontWeight: theme.typography.weights.semibold,
    maxWidth: 120,
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
