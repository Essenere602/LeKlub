import {
  Keyboard,
  KeyboardAvoidingView,
  Modal,
  Platform,
  Pressable,
  ScrollView,
  StyleSheet,
  TouchableWithoutFeedback,
  View,
} from 'react-native';
import { useState } from 'react';

import { theme } from '../../config/theme';
import { CreateReportPayload, ReportReason } from '../../types/feed.types';
import { AppButton } from '../ui/AppButton';
import { AppInput } from '../ui/AppInput';
import { AppText } from '../ui/AppText';
import { ErrorMessage } from '../ui/ErrorMessage';

const REASONS: Array<{ label: string; value: ReportReason }> = [
  { label: 'Spam', value: 'spam' },
  { label: 'Insultes', value: 'insults' },
  { label: 'Harcèlement', value: 'harassment' },
  { label: 'Contenu haineux', value: 'hate_content' },
  { label: 'Contenu inapproprié', value: 'inappropriate_content' },
  { label: 'Autre', value: 'other' },
];

type ReportContentModalProps = {
  visible: boolean;
  targetLabel: 'Post' | 'Commentaire';
  loading?: boolean;
  error?: string | null;
  onClose: () => void;
  onSubmit: (payload: CreateReportPayload) => Promise<void>;
};

export function ReportContentModal({
  error,
  loading = false,
  onClose,
  onSubmit,
  targetLabel,
  visible,
}: ReportContentModalProps) {
  const [reason, setReason] = useState<ReportReason>('spam');
  const [details, setDetails] = useState('');

  async function submit() {
    const trimmedDetails = details.trim();

    try {
      await onSubmit({
        reason,
        details: trimmedDetails === '' ? null : trimmedDetails,
      });
      setDetails('');
      setReason('spam');
    } catch {
      // The parent screen owns API error rendering in order to keep this modal reusable.
    }
  }

  return (
    <Modal animationType="fade" transparent visible={visible} onRequestClose={onClose}>
      <KeyboardAvoidingView
        behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
        style={styles.keyboard}
      >
        <TouchableWithoutFeedback accessible={false} onPress={Keyboard.dismiss}>
          <View style={styles.overlay}>
            <View style={styles.panel}>
              <ScrollView
                bounces={false}
                contentContainerStyle={styles.scrollContent}
                keyboardShouldPersistTaps="handled"
                showsVerticalScrollIndicator={false}
              >
                <View style={styles.header}>
                  <AppText style={styles.kicker}>Signalement</AppText>
                  <AppText variant="title">Signaler ce {targetLabel.toLowerCase()}</AppText>
                  <AppText variant="muted">Le signalement sera transmis à la modération.</AppText>
                </View>

                <View style={styles.reasons}>
                  {REASONS.map((item) => (
                    <Pressable
                      accessibilityRole="button"
                      key={item.value}
                      onPress={() => setReason(item.value)}
                      style={({ pressed }) => [
                        styles.reasonButton,
                        reason === item.value && styles.reasonButtonActive,
                        pressed && styles.pressed,
                      ]}
                    >
                      <AppText style={reason === item.value ? styles.reasonLabelActive : styles.reasonLabel}>
                        {item.label}
                      </AppText>
                    </Pressable>
                  ))}
                </View>

                <AppInput
                  autoCapitalize="sentences"
                  blurOnSubmit
                  label="Précision optionnelle"
                  maxLength={500}
                  multiline
                  onChangeText={setDetails}
                  onSubmitEditing={Keyboard.dismiss}
                  placeholder="Contexte utile pour la modération..."
                  returnKeyType="done"
                  style={styles.textArea}
                  textAlignVertical="top"
                  value={details}
                />

                <ErrorMessage message={error ?? null} />
              </ScrollView>

              <View style={styles.actions}>
                <AppButton label="Annuler" onPress={onClose} variant="secondary" style={styles.actionButton} />
                <AppButton label="Envoyer" loading={loading} onPress={submit} style={styles.actionButton} />
              </View>
            </View>
          </View>
        </TouchableWithoutFeedback>
      </KeyboardAvoidingView>
    </Modal>
  );
}

const styles = StyleSheet.create({
  keyboard: {
    flex: 1,
  },
  overlay: {
    backgroundColor: 'rgba(0, 0, 0, 0.72)',
    flex: 1,
    justifyContent: 'flex-end',
    padding: theme.spacing.lg,
  },
  panel: {
    backgroundColor: theme.colors.surface,
    borderColor: theme.colors.border,
    borderRadius: theme.radius.lg,
    borderWidth: 1,
    gap: theme.spacing.lg,
    maxHeight: '88%',
    padding: theme.spacing.lg,
  },
  scrollContent: {
    gap: theme.spacing.lg,
    paddingBottom: theme.spacing.sm,
  },
  header: {
    gap: theme.spacing.sm,
  },
  kicker: {
    color: theme.colors.accent,
    fontSize: theme.typography.sizes.sm,
    fontWeight: theme.typography.weights.bold,
    textTransform: 'uppercase',
  },
  reasons: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: theme.spacing.sm,
  },
  reasonButton: {
    backgroundColor: theme.colors.surfaceElevated,
    borderColor: theme.colors.border,
    borderRadius: 999,
    borderWidth: 1,
    paddingHorizontal: theme.spacing.md,
    paddingVertical: theme.spacing.sm,
  },
  reasonButtonActive: {
    backgroundColor: theme.colors.accent,
    borderColor: theme.colors.accent,
  },
  reasonLabel: {
    color: theme.colors.text.primary,
    fontSize: theme.typography.sizes.sm,
    fontWeight: theme.typography.weights.semibold,
  },
  reasonLabelActive: {
    color: theme.colors.text.inverse,
    fontSize: theme.typography.sizes.sm,
    fontWeight: theme.typography.weights.bold,
  },
  pressed: {
    opacity: 0.78,
  },
  textArea: {
    minHeight: 88,
    paddingTop: theme.spacing.md,
  },
  actions: {
    flexDirection: 'row',
    gap: theme.spacing.md,
  },
  actionButton: {
    flex: 1,
  },
});
