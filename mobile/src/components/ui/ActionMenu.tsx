import { Ionicons } from '@expo/vector-icons';
import { Modal, Pressable, StyleSheet, View } from 'react-native';
import { useState } from 'react';

import { theme } from '../../config/theme';
import { AppText } from './AppText';

export type ActionMenuItem = {
  label: string;
  icon: keyof typeof Ionicons.glyphMap;
  onPress: () => void;
  destructive?: boolean;
  disabled?: boolean;
};

type ActionMenuProps = {
  items: ActionMenuItem[];
  accessibilityLabel?: string;
};

export function ActionMenu({ accessibilityLabel = 'Actions', items }: ActionMenuProps) {
  const [visible, setVisible] = useState(false);

  if (items.length === 0) {
    return null;
  }

  function close() {
    setVisible(false);
  }

  return (
    <>
      <Pressable
        accessibilityLabel={accessibilityLabel}
        accessibilityRole="button"
        hitSlop={10}
        onPress={(event) => {
          event.stopPropagation();
          setVisible(true);
        }}
        style={({ pressed }) => [styles.trigger, pressed && styles.pressed]}
      >
        <Ionicons color={theme.colors.text.secondary} name="ellipsis-horizontal" size={20} />
      </Pressable>

      <Modal animationType="fade" transparent visible={visible} onRequestClose={close}>
        <Pressable style={styles.overlay} onPress={close}>
          <View style={styles.panel}>
            {items.map((item) => (
              <Pressable
                accessibilityRole="button"
                disabled={item.disabled}
                key={item.label}
                onPress={() => {
                  close();
                  item.onPress();
                }}
                style={({ pressed }) => [
                  styles.item,
                  item.disabled && styles.disabled,
                  pressed && !item.disabled && styles.pressed,
                ]}
              >
                <Ionicons
                  color={item.destructive ? theme.colors.danger : theme.colors.text.primary}
                  name={item.icon}
                  size={20}
                />
                <AppText style={[styles.itemLabel, item.destructive && styles.destructiveLabel]}>
                  {item.label}
                </AppText>
              </Pressable>
            ))}
          </View>
        </Pressable>
      </Modal>
    </>
  );
}

const styles = StyleSheet.create({
  trigger: {
    alignItems: 'center',
    backgroundColor: theme.colors.surfaceElevated,
    borderColor: theme.colors.borderSoft,
    borderRadius: theme.radius.full,
    borderWidth: 1,
    height: 36,
    justifyContent: 'center',
    width: 36,
  },
  overlay: {
    backgroundColor: 'rgba(0, 0, 0, 0.56)',
    flex: 1,
    justifyContent: 'flex-end',
    padding: theme.spacing.lg,
  },
  panel: {
    backgroundColor: theme.colors.surface,
    borderColor: theme.colors.border,
    borderRadius: theme.radius.lg,
    borderWidth: 1,
    overflow: 'hidden',
  },
  item: {
    alignItems: 'center',
    flexDirection: 'row',
    gap: theme.spacing.md,
    minHeight: 54,
    paddingHorizontal: theme.spacing.lg,
  },
  itemLabel: {
    flex: 1,
    fontSize: theme.typography.sizes.md,
    fontWeight: theme.typography.weights.semibold,
  },
  destructiveLabel: {
    color: theme.colors.danger,
  },
  disabled: {
    opacity: 0.45,
  },
  pressed: {
    opacity: 0.75,
  },
});
