import { Alert, Pressable, StyleSheet, View } from 'react-native';
import { useState } from 'react';

import { AppButton } from '../ui/AppButton';
import { AppInput } from '../ui/AppInput';
import { theme } from '../../config/theme';
import { Post, ReactionType } from '../../types/feed.types';
import { AppText } from '../ui/AppText';
import { ReactionButtons } from './ReactionButtons';

type PostCardProps = {
  post: Post;
  disabled?: boolean;
  canManage?: boolean;
  onOpen?: () => void;
  onReact: (type: ReactionType) => void;
  onRemoveReaction: () => void;
  onUpdate?: (content: string) => Promise<void>;
  onDelete?: () => Promise<void>;
};

export function PostCard({
  canManage = false,
  disabled = false,
  onDelete,
  onOpen,
  onReact,
  onRemoveReaction,
  onUpdate,
  post,
}: PostCardProps) {
  const [isEditing, setIsEditing] = useState(false);
  const [editContent, setEditContent] = useState(post.content);
  const [isSaving, setIsSaving] = useState(false);
  const [isDeleting, setIsDeleting] = useState(false);

  async function saveEdit() {
    const trimmedContent = editContent.trim();

    if (!trimmedContent) {
      Alert.alert('Contenu requis', 'Le Post ne peut pas être vide.');
      return;
    }

    if (!onUpdate) {
      return;
    }

    setIsSaving(true);

    try {
      await onUpdate(trimmedContent);
      setIsEditing(false);
    } finally {
      setIsSaving(false);
    }
  }

  function confirmDelete() {
    if (!onDelete) {
      return;
    }

    Alert.alert(
      'Supprimer le Post',
      'Cette action rendra le Post invisible dans le Feed.',
      [
        { text: 'Annuler', style: 'cancel' },
        {
          text: 'Supprimer',
          style: 'destructive',
          onPress: async () => {
            setIsDeleting(true);

            try {
              await onDelete();
            } finally {
              setIsDeleting(false);
            }
          },
        },
      ],
    );
  }

  return (
    <Pressable
      accessibilityRole={onOpen ? 'button' : undefined}
      disabled={isEditing || !onOpen}
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

      {isEditing ? (
        <View style={styles.editForm}>
          <AppInput
            autoCapitalize="sentences"
            label="Modifier le Post"
            maxLength={1000}
            multiline
            onChangeText={setEditContent}
            style={styles.textArea}
            textAlignVertical="top"
            value={editContent}
          />
          <View style={styles.actions}>
            <AppButton label="Annuler" onPress={() => {
              setEditContent(post.content);
              setIsEditing(false);
            }} variant="secondary" />
            <AppButton label="Enregistrer" loading={isSaving} onPress={saveEdit} />
          </View>
        </View>
      ) : (
        <AppText style={styles.content}>{post.content}</AppText>
      )}

      <View style={styles.meta}>
        <AppText variant="muted">{post.commentsCount} commentaire{post.commentsCount > 1 ? 's' : ''}</AppText>
      </View>

      {canManage && !isEditing ? (
        <View style={styles.actions}>
          <AppButton label="Modifier" onPress={() => setIsEditing(true)} variant="secondary" />
          <AppButton label="Supprimer" loading={isDeleting} onPress={confirmDelete} variant="ghost" />
        </View>
      ) : null}

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
  editForm: {
    gap: theme.spacing.md,
  },
  textArea: {
    minHeight: 88,
    paddingTop: theme.spacing.md,
  },
  actions: {
    flexDirection: 'row',
    gap: theme.spacing.md,
  },
});
