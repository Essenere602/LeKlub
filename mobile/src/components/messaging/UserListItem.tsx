import { Pressable, StyleSheet, View } from 'react-native';

import { theme } from '../../config/theme';
import { MessagingUser } from '../../types/userDirectory.types';
import { AppText } from '../ui/AppText';
import { UserAvatar } from './UserAvatar';

type UserListItemProps = {
  user: MessagingUser;
  onPress: () => void;
};

export function UserListItem({ user, onPress }: UserListItemProps) {
  const displayName = user.displayName ?? user.username;

  return (
    <Pressable
      onPress={onPress}
      style={({ pressed }) => [styles.card, pressed && styles.pressed]}
    >
      <UserAvatar label={displayName} uri={user.avatarUrl} />
      <View style={styles.body}>
        <AppText style={styles.name}>{displayName}</AppText>
        <AppText style={styles.username}>@{user.username}</AppText>
      </View>
    </Pressable>
  );
}

const styles = StyleSheet.create({
  card: {
    alignItems: 'center',
    backgroundColor: theme.colors.surface,
    borderColor: theme.colors.border,
    borderRadius: theme.radius.lg,
    borderWidth: 1,
    flexDirection: 'row',
    gap: theme.spacing.md,
    padding: theme.spacing.md,
  },
  pressed: {
    opacity: 0.82,
  },
  body: {
    flex: 1,
    gap: theme.spacing.xs,
  },
  name: {
    fontSize: theme.typography.sizes.md,
    fontWeight: theme.typography.weights.bold,
  },
  username: {
    color: theme.colors.text.muted,
    fontSize: theme.typography.sizes.sm,
  },
});
