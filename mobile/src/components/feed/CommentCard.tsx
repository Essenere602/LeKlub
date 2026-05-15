import { StyleSheet, View } from 'react-native';

import { theme } from '../../config/theme';
import { Commentaire } from '../../types/feed.types';
import { AppText } from '../ui/AppText';

type CommentCardProps = {
  comment: Commentaire;
};

export function CommentCard({ comment }: CommentCardProps) {
  return (
    <View style={styles.card}>
      <View style={styles.header}>
        <AppText variant="label">@{comment.author.username}</AppText>
        <AppText variant="muted">{formatDate(comment.createdAt)}</AppText>
      </View>
      <AppText>{comment.content}</AppText>
    </View>
  );
}

function formatDate(value: string): string {
  const date = new Date(value);

  if (Number.isNaN(date.getTime())) {
    return value;
  }

  return date.toLocaleDateString('fr-FR', {
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
    month: 'short',
  });
}

const styles = StyleSheet.create({
  card: {
    backgroundColor: theme.colors.surfaceElevated,
    borderColor: theme.colors.border,
    borderRadius: theme.radius.md,
    borderWidth: 1,
    gap: theme.spacing.sm,
    padding: theme.spacing.md,
  },
  header: {
    flexDirection: 'row',
    gap: theme.spacing.sm,
    justifyContent: 'space-between',
  },
});
