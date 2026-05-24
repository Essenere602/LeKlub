import { Alert, StyleSheet, View } from 'react-native';
import { useState } from 'react';

import { theme } from '../../config/theme';
import { Commentaire } from '../../types/feed.types';
import { AppButton } from '../ui/AppButton';
import { AppInput } from '../ui/AppInput';
import { ActionMenu, ActionMenuItem } from '../ui/ActionMenu';
import { AppText } from '../ui/AppText';
import { UserAvatar } from '../messaging/UserAvatar';

type CommentCardProps = {
  comment: Commentaire;
  canManage?: boolean;
  onUpdate?: (content: string) => Promise<void>;
  onDelete?: () => Promise<void>;
  onReport?: () => void;
};

export function CommentCard({ canManage = false, comment, onDelete, onReport, onUpdate }: CommentCardProps) {
  const [isEditing, setIsEditing] = useState(false);
  const [editContent, setEditContent] = useState(comment.content);
  const [isSaving, setIsSaving] = useState(false);
  const [isDeleting, setIsDeleting] = useState(false);

  async function saveEdit() {
    const trimmedContent = editContent.trim();

    if (!trimmedContent) {
      Alert.alert('Contenu requis', 'Le Commentaire ne peut pas être vide.');
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
      'Supprimer le Commentaire',
      'Cette action rendra le Commentaire invisible.',
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
    <View style={styles.card}>
      <View style={styles.header}>
        <View style={styles.identity}>
          <UserAvatar label={comment.author.username} size={34} uri={comment.author.avatarUrl} />
          <View style={styles.author}>
            <AppText style={styles.username}>@{comment.author.username}</AppText>
            <AppText style={styles.date}>{formatDate(comment.createdAt)}</AppText>
          </View>
        </View>
        <ActionMenu items={menuItems} accessibilityLabel="Actions du Commentaire" />
      </View>
      {isEditing ? (
        <View style={styles.editForm}>
          <AppInput
            autoCapitalize="sentences"
            label="Modifier le Commentaire"
            maxLength={500}
            multiline
            onChangeText={setEditContent}
            style={styles.textArea}
            textAlignVertical="top"
            value={editContent}
          />
          <View style={styles.actions}>
            <AppButton label="Annuler" onPress={() => {
              setEditContent(comment.content);
              setIsEditing(false);
            }} variant="secondary" />
            <AppButton label="Enregistrer" loading={isSaving} onPress={saveEdit} />
          </View>
        </View>
      ) : (
        <AppText style={styles.content}>{comment.content}</AppText>
      )}
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
    borderColor: theme.colors.borderSoft,
    borderRadius: theme.radius.lg,
    borderWidth: 1,
    gap: theme.spacing.md,
    padding: theme.spacing.lg,
  },
  header: {
    alignItems: 'center',
    flexDirection: 'row',
    justifyContent: 'space-between',
  },
  identity: {
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
    fontSize: theme.typography.sizes.sm,
    fontWeight: theme.typography.weights.bold,
  },
  date: {
    color: theme.colors.text.muted,
    fontSize: theme.typography.sizes.xs,
  },
  content: {
    color: theme.colors.text.secondary,
    lineHeight: 22,
  },
  editForm: {
    gap: theme.spacing.md,
  },
  textArea: {
    minHeight: 74,
    paddingTop: theme.spacing.md,
  },
  actions: {
    flexDirection: 'row',
    gap: theme.spacing.md,
  },
});
