import { Pressable, StyleSheet, View } from 'react-native';

import { theme } from '../../config/theme';
import { Post, ReactionType } from '../../types/feed.types';
import { AppText } from '../ui/AppText';
import { ReactionButtons } from './ReactionButtons';

type PostCardProps = {
  post: Post;
  disabled?: boolean;
  onOpen?: () => void;
  onReact: (type: ReactionType) => void;
  onRemoveReaction: () => void;
};

export function PostCard({ disabled = false, onOpen, onReact, onRemoveReaction, post }: PostCardProps) {
  return (
    <Pressable
      accessibilityRole={onOpen ? 'button' : undefined}
      disabled={!onOpen}
      onPress={onOpen}
      style={({ pressed }) => [styles.card, pressed && onOpen && styles.pressed]}
    >
      <View style={styles.header}>
        <View style={styles.avatar}>
          <AppText style={styles.avatarText}>{post.author.username.slice(0, 1).toUpperCase()}</AppText>
        </View>
        <View style={styles.author}>
          <AppText variant="label">@{post.author.username}</AppText>
          <AppText variant="muted">{formatDate(post.createdAt)}</AppText>
        </View>
      </View>

      <AppText style={styles.content}>{post.content}</AppText>

      <View style={styles.meta}>
        <AppText variant="muted">{post.commentsCount} commentaire{post.commentsCount > 1 ? 's' : ''}</AppText>
      </View>

      <ReactionButtons
        disabled={disabled}
        dislikesCount={post.dislikesCount}
        likesCount={post.likesCount}
        onReact={onReact}
        onRemove={onRemoveReaction}
      />
    </Pressable>
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
    backgroundColor: theme.colors.surface,
    borderColor: theme.colors.border,
    borderRadius: theme.radius.md,
    borderWidth: 1,
    gap: theme.spacing.md,
    padding: theme.spacing.lg,
  },
  pressed: {
    opacity: 0.84,
  },
  header: {
    alignItems: 'center',
    flexDirection: 'row',
    gap: theme.spacing.md,
  },
  avatar: {
    alignItems: 'center',
    backgroundColor: theme.colors.accentSoft,
    borderColor: theme.colors.accent,
    borderRadius: 20,
    borderWidth: 1,
    height: 40,
    justifyContent: 'center',
    width: 40,
  },
  avatarText: {
    color: theme.colors.accent,
    fontWeight: theme.typography.weights.bold,
  },
  author: {
    flex: 1,
    gap: theme.spacing.xs,
  },
  content: {
    fontSize: theme.typography.sizes.md,
    lineHeight: 23,
  },
  meta: {
    borderTopColor: theme.colors.border,
    borderTopWidth: 1,
    paddingTop: theme.spacing.md,
  },
});
