import { Ionicons } from '@expo/vector-icons';
import { Pressable, StyleSheet, View } from 'react-native';

import { theme } from '../../config/theme';
import { ReactionType } from '../../types/feed.types';
import { AppText } from '../ui/AppText';

type ReactionButtonsProps = {
  likesCount: number;
  dislikesCount: number;
  disabled?: boolean;
  onReact: (type: ReactionType) => void;
};

export function ReactionButtons({
  disabled = false,
  dislikesCount,
  likesCount,
  onReact,
}: ReactionButtonsProps) {
  return (
    <View style={styles.container}>
      <ReactionButton
        icon="thumbs-up-outline"
        disabled={disabled}
        label="Like"
        value={likesCount}
        onPress={() => onReact('like')}
      />
      <ReactionButton
        icon="thumbs-down-outline"
        disabled={disabled}
        label="Dislike"
        value={dislikesCount}
        onPress={() => onReact('dislike')}
      />
    </View>
  );
}

type ReactionButtonProps = {
  disabled: boolean;
  icon: keyof typeof Ionicons.glyphMap;
  label: string;
  onPress: () => void;
  value: number;
};

function ReactionButton({ disabled, icon, label, onPress, value }: ReactionButtonProps) {
  return (
    <Pressable
      accessibilityRole="button"
      accessibilityLabel={`${label} ${value}`}
      disabled={disabled}
      onPress={(event) => {
        event.stopPropagation();
        onPress();
      }}
      style={({ pressed }) => [
        styles.button,
        disabled && styles.disabled,
        pressed && !disabled && styles.pressed,
      ]}
    >
      <Ionicons color={theme.colors.accent} name={icon} size={17} />
      <AppText style={styles.value}>{value}</AppText>
    </Pressable>
  );
}

const styles = StyleSheet.create({
  container: {
    flexDirection: 'row',
    gap: theme.spacing.sm,
  },
  button: {
    alignItems: 'center',
    backgroundColor: theme.colors.accentSoft,
    borderColor: theme.colors.accent,
    borderRadius: theme.radius.full,
    borderWidth: 1,
    flexDirection: 'row',
    gap: theme.spacing.xs,
    minHeight: 36,
    paddingHorizontal: theme.spacing.md,
  },
  disabled: {
    opacity: 0.55,
  },
  pressed: {
    opacity: 0.78,
  },
  value: {
    color: theme.colors.accent,
    fontSize: theme.typography.sizes.sm,
    fontWeight: theme.typography.weights.bold,
  },
});
