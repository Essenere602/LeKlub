import { Alert, Pressable, StyleSheet, View } from 'react-native';
import { Ionicons } from '@expo/vector-icons';

import { theme } from '../../config/theme';
import { AdminComment, AdminPost } from '../../types/admin.types';
import { AppCard } from '../ui/AppCard';
import { AppText } from '../ui/AppText';
import { StatusBadge } from '../ui/StatusBadge';

type AdminModerationCardProps = {
  item: AdminPost | AdminComment;
  type: 'post' | 'comment';
  deleting?: boolean;
  onDelete: () => void;
};

export function AdminModerationCard({ deleting = false, item, onDelete, type }: AdminModerationCardProps) {
  const label = type === 'post' ? 'Post' : 'Commentaire';

  function confirmDelete() {
    Alert.alert(
      `Supprimer ${label.toLowerCase()}`,
      'Cette action applique une suppression logique. Le contenu ne sera plus visible côté utilisateur.',
      [
        { text: 'Annuler', style: 'cancel' },
        { text: 'Supprimer', onPress: onDelete, style: 'destructive' },
      ],
    );
  }

  return (
    <AppCard style={styles.card}>
      <View style={styles.header}>
        <View style={styles.author}>
          <StatusBadge label={label} variant={type === 'post' ? 'accent' : 'warning'} />
          <AppText variant="muted">@{item.author.username} · {formatDate(item.createdAt)}</AppText>
        </View>
        <Pressable
          accessibilityRole="button"
          disabled={deleting}
          onPress={confirmDelete}
          style={({ pressed }) => [styles.deleteButton, pressed && styles.pressed, deleting && styles.disabled]}
        >
          <Ionicons color={theme.colors.danger} name="trash-outline" size={18} />
        </Pressable>
      </View>

      <AppText style={styles.content}>{item.content}</AppText>

      {type === 'comment' && 'post' in item ? (
        <View style={styles.context}>
          <AppText variant="muted">Sur le post #{item.post.id}</AppText>
          <AppText variant="muted" numberOfLines={2}>{item.post.excerpt}</AppText>
        </View>
      ) : null}
    </AppCard>
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
    gap: theme.spacing.md,
  },
  header: {
    alignItems: 'flex-start',
    flexDirection: 'row',
    gap: theme.spacing.md,
    justifyContent: 'space-between',
  },
  author: {
    flex: 1,
    gap: theme.spacing.sm,
  },
  deleteButton: {
    alignItems: 'center',
    backgroundColor: 'rgba(255, 59, 92, 0.1)',
    borderColor: theme.colors.danger,
    borderRadius: theme.radius.md,
    borderWidth: 1,
    height: 40,
    justifyContent: 'center',
    width: 40,
  },
  pressed: {
    opacity: 0.76,
  },
  disabled: {
    opacity: 0.45,
  },
  content: {
    fontSize: theme.typography.sizes.md,
    lineHeight: 23,
  },
  context: {
    borderTopColor: theme.colors.border,
    borderTopWidth: 1,
    gap: theme.spacing.xs,
    paddingTop: theme.spacing.md,
  },
});
