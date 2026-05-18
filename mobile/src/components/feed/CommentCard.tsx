import { Alert, StyleSheet, View } from 'react-native';
import { useState } from 'react';

import { theme } from '../../config/theme';
import { Commentaire } from '../../types/feed.types';
import { AppButton } from '../ui/AppButton';
import { AppInput } from '../ui/AppInput';
import { AppText } from '../ui/AppText';

type CommentCardProps = {
  comment: Commentaire;
  canManage?: boolean;
  onUpdate?: (content: string) => Promise<void>;
  onDelete?: () => Promise<void>;
};

export function CommentCard({ canManage = false, comment, onDelete, onUpdate }: CommentCardProps) {
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

  return (
    <View style={styles.card}>
      <View style={styles.header}>
        <AppText variant="label">@{comment.author.username}</AppText>
        <AppText variant="muted">{formatDate(comment.createdAt)}</AppText>
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
        <AppText>{comment.content}</AppText>
      )}
      {canManage && !isEditing ? (
        <View style={styles.actions}>
          <AppButton label="Modifier" onPress={() => setIsEditing(true)} variant="secondary" />
          <AppButton label="Supprimer" loading={isDeleting} onPress={confirmDelete} variant="ghost" />
        </View>
      ) : null}
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
