import { Alert, Pressable, StyleSheet, View } from 'react-native';

import { theme } from '../../config/theme';
import { PrivateMessage } from '../../types/messaging.types';
import { AppText } from '../ui/AppText';

type MessageBubbleProps = {
  message: PrivateMessage;
  mine: boolean;
  onHideForMe?: () => Promise<void>;
};

export function MessageBubble({ message, mine, onHideForMe }: MessageBubbleProps) {
  const isRead = message.readAt !== null;

  function confirmHideForMe() {
    if (!onHideForMe) {
      return;
    }

    Alert.alert(
      'Supprimer pour moi',
      "Ce message disparaîtra uniquement de ton historique. L'autre participant pourra toujours le voir.",
      [
        { text: 'Annuler', style: 'cancel' },
        { text: 'Supprimer pour moi', onPress: () => void onHideForMe(), style: 'destructive' },
      ],
    );
  }

  return (
    <View style={[styles.row, mine ? styles.mineRow : styles.otherRow]}>
      <Pressable
        accessibilityRole="button"
        onLongPress={confirmHideForMe}
        style={[styles.bubble, mine ? styles.mineBubble : styles.otherBubble]}
      >
        {!mine ? <AppText style={styles.sender}>{message.sender.username}</AppText> : null}
        <AppText style={[styles.content, mine && styles.mineContent]}>{message.content}</AppText>
        <View style={styles.metaRow}>
          <AppText style={[styles.date, mine && styles.mineDate]}>{formatMessageDate(message.createdAt)}</AppText>
          {mine ? (
            <AppText
              accessibilityLabel={isRead ? 'Message lu' : 'Message envoyé non lu'}
              style={[styles.readReceipt, isRead && styles.readReceiptRead]}
            >
              {isRead ? '✓✓' : '✓'}
            </AppText>
          ) : null}
        </View>
      </Pressable>
    </View>
  );
}

function formatMessageDate(value: string): string {
  const date = new Date(value);

  if (Number.isNaN(date.getTime())) {
    return '';
  }

  return date.toLocaleTimeString('fr-FR', {
    hour: '2-digit',
    minute: '2-digit',
  });
}

const styles = StyleSheet.create({
  row: {
    flexDirection: 'row',
  },
  mineRow: {
    justifyContent: 'flex-end',
  },
  otherRow: {
    justifyContent: 'flex-start',
  },
  bubble: {
    borderRadius: theme.radius.lg,
    gap: theme.spacing.xs,
    maxWidth: '82%',
    padding: theme.spacing.md,
  },
  mineBubble: {
    backgroundColor: theme.colors.accent,
    borderBottomRightRadius: theme.radius.sm,
  },
  otherBubble: {
    backgroundColor: theme.colors.surfaceElevated,
    borderBottomLeftRadius: theme.radius.sm,
    borderColor: theme.colors.border,
    borderWidth: 1,
  },
  sender: {
    color: theme.colors.accent,
    fontSize: theme.typography.sizes.xs,
    fontWeight: theme.typography.weights.bold,
  },
  content: {
    color: theme.colors.text.primary,
    fontSize: theme.typography.sizes.md,
    lineHeight: 22,
  },
  mineContent: {
    color: theme.colors.text.inverse,
  },
  metaRow: {
    alignItems: 'center',
    flexDirection: 'row',
    gap: theme.spacing.xs,
    justifyContent: 'flex-end',
  },
  date: {
    color: theme.colors.text.muted,
    fontSize: theme.typography.sizes.xs,
    textAlign: 'right',
  },
  mineDate: {
    color: 'rgba(8, 11, 10, 0.7)',
  },
  readReceipt: {
    color: 'rgba(8, 11, 10, 0.62)',
    fontSize: theme.typography.sizes.sm,
    fontWeight: theme.typography.weights.bold,
    lineHeight: 16,
  },
  readReceiptRead: {
    color: theme.colors.football.cyan,
  },
});
