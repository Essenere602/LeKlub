import { Pressable, StyleSheet, View } from 'react-native';

import { theme } from '../../config/theme';
import { ReactionType } from '../../types/feed.types';
import { AppText } from '../ui/AppText';

type ReactionButtonsProps = {
  likesCount: number;
  dislikesCount: number;
  disabled?: boolean;
  onReact: (type: ReactionType) => void;
  onRemove: () => void;
};

export function ReactionButtons({
  disabled = false,
  dislikesCount,
  likesCount,
  onReact,
  onRemove,
}: ReactionButtonsProps) {
  return (
    <View style={styles.container}>
      <ReactionButton
        disabled={disabled}
        label={`Like ${likesCount}`}
        onPress={() => onReact('like')}
      />
      <ReactionButton
        disabled={disabled}
        label={`Dislike ${dislikesCount}`}
        onPress={() => onReact('dislike')}
      />
      <ReactionButton disabled={disabled} label="Retirer" onPress={onRemove} subdued />
    </View>
  );
}

type ReactionButtonProps = {
  disabled: boolean;
  label: string;
  onPress: () => void;
  subdued?: boolean;
};

function ReactionButton({ disabled, label, onPress, subdued = false }: ReactionButtonProps) {
  return (
    <Pressable
      accessibilityRole="button"
      disabled={disabled}
      onPress={onPress}
      style={({ pressed }) => [
        styles.button,
        subdued && styles.subdued,
        disabled && styles.disabled,
        pressed && !disabled && styles.pressed,
      ]}
    >
      <AppText style={[styles.label, subdued && styles.subduedLabel]}>{label}</AppText>
    </Pressable>
  );
}

const styles = StyleSheet.create({
  container: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: theme.spacing.sm,
  },
  button: {
    backgroundColor: theme.colors.accentSoft,
    borderColor: theme.colors.accent,
    borderRadius: theme.radius.sm,
    borderWidth: 1,
    paddingHorizontal: theme.spacing.md,
    paddingVertical: theme.spacing.sm,
  },
  subdued: {
    backgroundColor: theme.colors.surfaceElevated,
    borderColor: theme.colors.border,
  },
  disabled: {
    opacity: 0.55,
  },
  pressed: {
    opacity: 0.78,
  },
  label: {
    color: theme.colors.accent,
    fontSize: theme.typography.sizes.sm,
    fontWeight: theme.typography.weights.semibold,
  },
  subduedLabel: {
    color: theme.colors.text.secondary,
  },
});
