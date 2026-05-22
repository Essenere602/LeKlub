import { Pressable, StyleSheet, View } from 'react-native';

import { theme } from '../../config/theme';
import { Conversation } from '../../types/messaging.types';
import { AppText } from '../ui/AppText';
import { UserAvatar } from './UserAvatar';

type ConversationCardProps = {
  conversation: Conversation;
  onPress: () => void;
};

export function ConversationCard({ conversation, onPress }: ConversationCardProps) {
  const participantName = conversation.participant?.username ?? 'Utilisateur';
  const lastMessage = conversation.lastMessage?.content ?? 'Aucun message pour le moment';
  const hasUnread = conversation.unreadCount > 0;

  return (
    <Pressable
      onPress={onPress}
      style={({ pressed }) => [styles.card, hasUnread && styles.unreadCard, pressed && styles.pressed]}
    >
      <UserAvatar label={participantName} />

      <View style={styles.body}>
        <View style={styles.header}>
          <AppText style={styles.name}>{participantName}</AppText>
          {hasUnread ? (
            <View style={styles.badge}>
              <AppText style={styles.badgeText}>{conversation.unreadCount}</AppText>
            </View>
          ) : null}
        </View>
        <AppText numberOfLines={2} style={[styles.preview, hasUnread && styles.unreadPreview]}>{lastMessage}</AppText>
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
  unreadCard: {
    borderColor: theme.colors.accent,
    backgroundColor: theme.colors.surfaceElevated,
  },
  pressed: {
    opacity: 0.82,
  },
  body: {
    flex: 1,
    gap: theme.spacing.xs,
    minWidth: 0,
  },
  header: {
    alignItems: 'center',
    flexDirection: 'row',
    gap: theme.spacing.sm,
  },
  name: {
    flex: 1,
    fontSize: theme.typography.sizes.md,
    fontWeight: theme.typography.weights.bold,
  },
  preview: {
    color: theme.colors.text.muted,
    fontSize: theme.typography.sizes.sm,
    lineHeight: 19,
  },
  unreadPreview: {
    color: theme.colors.text.secondary,
  },
  badge: {
    alignItems: 'center',
    backgroundColor: theme.colors.accent,
    borderRadius: 999,
    minWidth: 24,
    paddingHorizontal: theme.spacing.xs,
    paddingVertical: 2,
  },
  badgeText: {
    color: theme.colors.text.inverse,
    fontSize: theme.typography.sizes.xs,
    fontWeight: theme.typography.weights.bold,
  },
});
