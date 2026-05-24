import { Alert, Pressable, StyleSheet, View } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useState } from 'react';

import { AppButton } from '../ui/AppButton';
import { AppInput } from '../ui/AppInput';
import { ActionMenu, ActionMenuItem } from '../ui/ActionMenu';
import { theme } from '../../config/theme';
import { Post, ReactionType } from '../../types/feed.types';
import { formatShortDateTime } from '../../utils/dateFormat';
import { AppText } from '../ui/AppText';
import { UserAvatar } from '../messaging/UserAvatar';
import { ReactionButtons } from './ReactionButtons';

type PostCardProps = {
  post: Post;
  disabled?: boolean;
  canManage?: boolean;
  onOpen?: () => void;
  onReact: (type: ReactionType) => void;
  onReport?: () => void;
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
  onReport,
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

  const menuItems: ActionMenuItem[] = [];

  if (!isEditing && canManage && onUpdate) {
    menuItems.push({
      icon: 'create-outline',
      label: 'Modifier',
      onPress: () => setIsEditing(true),
    });
  }

  if (!isEditing && onRemoveReaction) {
    menuItems.push({
      disabled,
      icon: 'close-circle-outline',
      label: 'Retirer ma réaction',
      onPress: onRemoveReaction,
    });
  }

  if (!isEditing && canManage && onDelete) {
    menuItems.push({
      destructive: true,
      disabled: isDeleting,
      icon: 'trash-outline',
      label: 'Supprimer',
      onPress: confirmDelete,
    });
  }

  if (!isEditing && !canManage && onReport) {
    menuItems.push({
      icon: 'flag-outline',
      label: 'Signaler',
      onPress: onReport,
    });
  }

  return (
    <Pressable
      accessibilityRole={onOpen ? 'button' : undefined}
      disabled={isEditing || !onOpen}
      onPress={onOpen}
      style={({ pressed }) => [styles.card, pressed && onOpen && styles.pressed]}
    >
      <View style={styles.header}>
        <View style={styles.authorBlock}>
          <UserAvatar label={post.author.username} size={40} uri={post.author.avatarUrl} />
          <View style={styles.author}>
            <AppText style={styles.username}>@{post.author.username}</AppText>
            <AppText style={styles.date}>{formatShortDateTime(post.createdAt)}</AppText>
          </View>
        </View>
        <ActionMenu items={menuItems} accessibilityLabel="Actions du Post" />
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

      {!isEditing ? (
        <View style={styles.socialBar}>
          <ReactionButtons
            disabled={disabled}
            dislikesCount={post.dislikesCount}
            likesCount={post.likesCount}
            onReact={onReact}
          />
          {onOpen ? (
            <Pressable
              accessibilityRole="button"
              onPress={onOpen}
              style={({ pressed }) => [styles.commentAction, pressed && styles.pressed]}
            >
              <Ionicons color={theme.colors.text.secondary} name="chatbubble-outline" size={17} />
              <AppText style={styles.commentText}>{post.commentsCount}</AppText>
            </Pressable>
          ) : (
            <View style={styles.commentAction}>
              <Ionicons color={theme.colors.text.secondary} name="chatbubble-outline" size={17} />
              <AppText style={styles.commentText}>{post.commentsCount}</AppText>
            </View>
          )}
        </View>
      ) : null}
    </Pressable>
  );
}

const styles = StyleSheet.create({
  card: {
    backgroundColor: theme.colors.surface,
    borderColor: theme.colors.borderSoft,
    borderRadius: theme.radius.lg,
    borderWidth: 1,
    gap: theme.spacing.lg,
    padding: theme.spacing.xl,
  },
  pressed: {
    opacity: 0.84,
  },
  header: {
    alignItems: 'center',
    flexDirection: 'row',
    justifyContent: 'space-between',
    gap: theme.spacing.md,
  },
  authorBlock: {
    alignItems: 'center',
    flex: 1,
    flexDirection: 'row',
    gap: theme.spacing.md,
  },
  author: {
    flex: 1,
    gap: theme.spacing.xs,
  },
  username: {
    fontSize: theme.typography.sizes.md,
    fontWeight: theme.typography.weights.bold,
  },
  date: {
    color: theme.colors.text.muted,
    fontSize: theme.typography.sizes.xs,
  },
  content: {
    fontSize: theme.typography.sizes.md,
    lineHeight: 24,
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
  socialBar: {
    alignItems: 'center',
    borderTopColor: theme.colors.borderSoft,
    borderTopWidth: 1,
    flexDirection: 'row',
    justifyContent: 'space-between',
    paddingTop: theme.spacing.md,
  },
  commentAction: {
    alignItems: 'center',
    backgroundColor: theme.colors.surfaceElevated,
    borderColor: theme.colors.borderSoft,
    borderRadius: theme.radius.full,
    borderWidth: 1,
    flexDirection: 'row',
    gap: theme.spacing.xs,
    minHeight: 36,
    paddingHorizontal: theme.spacing.md,
  },
  commentText: {
    color: theme.colors.text.secondary,
    fontSize: theme.typography.sizes.sm,
    fontWeight: theme.typography.weights.bold,
  },
});
