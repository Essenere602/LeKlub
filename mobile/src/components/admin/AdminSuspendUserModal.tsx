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
import { useEffect, useState } from 'react';

import { theme } from '../../config/theme';
import { AdminUser, SuspendUserPayload } from '../../types/admin.types';
import { AppButton } from '../ui/AppButton';
import { AppInput } from '../ui/AppInput';
import { AppText } from '../ui/AppText';
import { ErrorMessage } from '../ui/ErrorMessage';

const DURATIONS: Array<{ label: string; value: SuspendUserPayload['durationDays'] }> = [
  { label: '1 jour', value: 1 },
  { label: '7 jours', value: 7 },
  { label: '30 jours', value: 30 },
];

type AdminSuspendUserModalProps = {
  visible: boolean;
  user: AdminUser | null;
  loading?: boolean;
  error?: string | null;
  onClose: () => void;
  onSubmit: (payload: SuspendUserPayload) => Promise<void>;
};

export function AdminSuspendUserModal({
  error,
  loading = false,
  onClose,
  onSubmit,
  user,
  visible,
}: AdminSuspendUserModalProps) {
  const [durationDays, setDurationDays] = useState<SuspendUserPayload['durationDays']>(7);
  const [reason, setReason] = useState('');
  const [localError, setLocalError] = useState<string | null>(null);

  useEffect(() => {
    if (!visible) {
      setDurationDays(7);
      setReason('');
      setLocalError(null);
    }
  }, [visible]);

  async function submit() {
    const trimmedReason = reason.trim();

    if (trimmedReason.length === 0) {
      setLocalError('La raison est obligatoire.');
      return;
    }

    if (trimmedReason.length > 500) {
      setLocalError('La raison est limitée à 500 caractères.');
      return;
    }

    setLocalError(null);
    await onSubmit({ durationDays, reason: trimmedReason });
  }

  return (
    <Modal animationType="fade" transparent visible={visible} onRequestClose={onClose}>
      <KeyboardAvoidingView behavior={Platform.OS === 'ios' ? 'padding' : 'height'} style={styles.keyboard}>
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
                  <AppText style={styles.kicker}>Suspension temporaire</AppText>
                  <AppText variant="title">@{user?.username ?? 'utilisateur'}</AppText>
                  <AppText variant="muted">La lecture reste possible, les actions d'écriture seront bloquées.</AppText>
                </View>

                <View style={styles.durationRow}>
                  {DURATIONS.map((duration) => (
                    <Pressable
                      accessibilityRole="button"
                      key={duration.value}
                      onPress={() => setDurationDays(duration.value)}
                      style={({ pressed }) => [
                        styles.durationButton,
                        durationDays === duration.value && styles.durationButtonActive,
                        pressed && styles.pressed,
                      ]}
                    >
                      <AppText style={durationDays === duration.value ? styles.durationLabelActive : styles.durationLabel}>
                        {duration.label}
                      </AppText>
                    </Pressable>
                  ))}
                </View>

                <AppInput
                  autoCapitalize="sentences"
                  label="Raison"
                  maxLength={500}
                  multiline
                  onChangeText={setReason}
                  placeholder="Explique la raison de la suspension..."
                  style={styles.textArea}
                  textAlignVertical="top"
                  value={reason}
                />

                <ErrorMessage message={localError ?? error ?? null} />
              </ScrollView>

              <View style={styles.actions}>
                <AppButton label="Annuler" onPress={onClose} style={styles.actionButton} variant="secondary" />
                <AppButton label="Suspendre" loading={loading} onPress={submit} style={styles.actionButton} />
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
  durationRow: {
    flexDirection: 'row',
    gap: theme.spacing.sm,
  },
  durationButton: {
    alignItems: 'center',
    backgroundColor: theme.colors.surfaceElevated,
    borderColor: theme.colors.border,
    borderRadius: theme.radius.md,
    borderWidth: 1,
    flex: 1,
    minHeight: 44,
    justifyContent: 'center',
    paddingHorizontal: theme.spacing.sm,
  },
  durationButtonActive: {
    backgroundColor: theme.colors.accent,
    borderColor: theme.colors.accent,
  },
  durationLabel: {
    color: theme.colors.text.primary,
    fontSize: theme.typography.sizes.sm,
    fontWeight: theme.typography.weights.bold,
  },
  durationLabelActive: {
    color: theme.colors.text.inverse,
    fontSize: theme.typography.sizes.sm,
    fontWeight: theme.typography.weights.bold,
  },
  pressed: {
    opacity: 0.78,
  },
  textArea: {
    minHeight: 110,
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
