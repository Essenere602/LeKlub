import { Pressable, StyleSheet, View } from 'react-native';
import { Ionicons } from '@expo/vector-icons';

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
        icon="thumbs-up-outline"
        label={`Like ${likesCount}`}
        onPress={() => onReact('like')}
      />
      <ReactionButton
        disabled={disabled}
        icon="thumbs-down-outline"
        label={`Dislike ${dislikesCount}`}
        onPress={() => onReact('dislike')}
      />
      <ReactionButton disabled={disabled} icon="close-circle-outline" label="Retirer" onPress={onRemove} subdued />
    </View>
  );
}

type ReactionButtonProps = {
  disabled: boolean;
  icon: keyof typeof Ionicons.glyphMap;
  label: string;
  onPress: () => void;
  subdued?: boolean;
};

function ReactionButton({ disabled, icon, label, onPress, subdued = false }: ReactionButtonProps) {
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
      <Ionicons color={subdued ? theme.colors.text.secondary : theme.colors.accent} name={icon} size={15} />
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
    flexDirection: 'row',
    gap: theme.spacing.xs,
    minHeight: 38,
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
