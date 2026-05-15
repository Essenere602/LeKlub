import { Pressable, StyleSheet, View } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { ComponentProps } from 'react';

import { theme } from '../../config/theme';
import { AppText } from '../ui/AppText';

export type FootballTabItem<TKey extends string> = {
  key: TKey;
  label: string;
  icon: ComponentProps<typeof Ionicons>['name'];
};

type FootballSectionTabsProps<TKey extends string> = {
  activeKey: TKey;
  items: Array<FootballTabItem<TKey>>;
  onChange: (key: TKey) => void;
};

export function FootballSectionTabs<TKey extends string>({
  activeKey,
  items,
  onChange,
}: FootballSectionTabsProps<TKey>) {
  return (
    <View style={styles.container}>
      {items.map((item) => {
        const active = item.key === activeKey;

        return (
          <Pressable
            accessibilityRole="button"
            key={item.key}
            onPress={() => onChange(item.key)}
            style={({ pressed }) => [
              styles.tab,
              active && styles.activeTab,
              pressed && styles.pressed,
            ]}
          >
            <Ionicons
              color={active ? theme.colors.football.blackPure : theme.colors.text.secondary}
              name={item.icon}
              size={16}
            />
            <AppText style={[styles.label, active && styles.activeLabel]}>{item.label}</AppText>
          </Pressable>
        );
      })}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    backgroundColor: theme.colors.football.surface,
    borderColor: theme.colors.football.border,
    borderRadius: theme.radius.md,
    borderWidth: 2,
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: theme.spacing.sm,
    padding: theme.spacing.sm,
  },
  tab: {
    alignItems: 'center',
    backgroundColor: theme.colors.football.card,
    borderColor: theme.colors.football.border,
    borderRadius: theme.radius.sm,
    borderWidth: 1,
    flexGrow: 1,
    flexShrink: 0,
    flexDirection: 'row',
    gap: theme.spacing.xs,
    justifyContent: 'center',
    minHeight: 44,
    minWidth: 126,
    paddingHorizontal: theme.spacing.sm,
  },
  activeTab: {
    backgroundColor: theme.colors.football.neon,
    borderColor: theme.colors.football.neon,
  },
  pressed: {
    opacity: 0.76,
  },
  label: {
    color: theme.colors.text.secondary,
    fontSize: theme.typography.sizes.sm,
    fontWeight: theme.typography.weights.semibold,
    includeFontPadding: false,
    lineHeight: 20,
  },
  activeLabel: {
    color: theme.colors.football.blackPure,
  },
});
