import { useState } from 'react';
import { KeyboardAvoidingView, Platform, ScrollView, StyleSheet, View } from 'react-native';
import { NativeStackScreenProps } from '@react-navigation/native-stack';

import { PasswordRulesChecklist } from '../../components/auth/PasswordRulesChecklist';
import { AppButton } from '../../components/ui/AppButton';
import { AppCard } from '../../components/ui/AppCard';
import { AppInput } from '../../components/ui/AppInput';
import { AppText } from '../../components/ui/AppText';
import { ErrorMessage } from '../../components/ui/ErrorMessage';
import { Screen } from '../../components/ui/Screen';
import { theme } from '../../config/theme';
import { ProfileStackParamList } from '../../navigation/navigation.types';
import { toApiError } from '../../services/api/apiError';
import { profileService } from '../../services/user/profileService';
import { isPasswordValid, passwordConfirmationMatches } from '../../utils/passwordValidation';

type ChangePasswordScreenProps = NativeStackScreenProps<ProfileStackParamList, 'ChangePassword'>;

type PasswordForm = {
  currentPassword: string;
  newPassword: string;
  newPasswordConfirmation: string;
};

const initialForm: PasswordForm = {
  currentPassword: '',
  newPassword: '',
  newPasswordConfirmation: '',
};

export function ChangePasswordScreen({ navigation }: ChangePasswordScreenProps) {
  const [form, setForm] = useState<PasswordForm>(initialForm);
  const [error, setError] = useState<string | null>(null);
  const [successMessage, setSuccessMessage] = useState<string | null>(null);
  const [isSaving, setIsSaving] = useState(false);

  function updateField(field: keyof PasswordForm, value: string) {
    setForm((currentForm) => ({ ...currentForm, [field]: value }));
    setError(null);
    setSuccessMessage(null);
  }

  async function submitPassword() {
    const validationError = validateForm(form);

    if (validationError !== null) {
      setError(validationError);
      setSuccessMessage(null);
      return;
    }

    setIsSaving(true);
    setError(null);
    setSuccessMessage(null);

    try {
      await profileService.changePassword(form);
      setForm(initialForm);
      setSuccessMessage('Mot de passe mis à jour. Les sessions longues ont été révoquées.');
    } catch (caughtError) {
      setError(toApiError(caughtError).message);
    } finally {
      setIsSaving(false);
    }
  }

  return (
    <Screen style={styles.screen}>
      <KeyboardAvoidingView
        behavior={Platform.OS === 'ios' ? 'padding' : undefined}
        style={styles.keyboard}
      >
        <ScrollView contentContainerStyle={styles.content} keyboardShouldPersistTaps="handled">
          <View style={styles.header}>
            <AppText style={styles.kicker}>Sécurité</AppText>
            <AppText variant="title">Mot de passe</AppText>
            <AppText variant="subtitle">Choisis un nouveau mot de passe robuste pour protéger ton compte.</AppText>
          </View>

          <AppCard style={styles.form}>
            <AppInput
              label="Ancien mot de passe"
              onChangeText={(value) => updateField('currentPassword', value)}
              secureTextEntry
              textContentType="password"
              value={form.currentPassword}
            />

            <AppInput
              label="Nouveau mot de passe"
              onChangeText={(value) => updateField('newPassword', value)}
              placeholder="Votre nouveau mot de passe"
              secureTextEntry
              textContentType="newPassword"
              value={form.newPassword}
            />
            <PasswordRulesChecklist password={form.newPassword} />

            <AppInput
              label="Confirmer le nouveau mot de passe"
              onChangeText={(value) => updateField('newPasswordConfirmation', value)}
              placeholder="Répéter le nouveau mot de passe"
              secureTextEntry
              textContentType="newPassword"
              value={form.newPasswordConfirmation}
            />
          </AppCard>

          <ErrorMessage message={error} />
          {successMessage ? <AppText style={styles.success}>{successMessage}</AppText> : null}

          <View style={styles.actions}>
            <AppButton label="Mettre à jour" loading={isSaving} onPress={submitPassword} />
            <AppButton label="Retour profil" onPress={() => navigation.goBack()} variant="secondary" />
          </View>
        </ScrollView>
      </KeyboardAvoidingView>
    </Screen>
  );
}

function validateForm(form: PasswordForm): string | null {
  if (!form.currentPassword || !form.newPassword || !form.newPasswordConfirmation) {
    return 'Tous les champs sont obligatoires.';
  }

  if (!isPasswordValid(form.newPassword)) {
    return 'Le nouveau mot de passe ne respecte pas encore toutes les règles.';
  }

  if (!passwordConfirmationMatches(form.newPassword, form.newPasswordConfirmation)) {
    return 'La confirmation ne correspond pas au nouveau mot de passe.';
  }

  return null;
}

const styles = StyleSheet.create({
  screen: {
    padding: 0,
  },
  keyboard: {
    flex: 1,
  },
  content: {
    gap: theme.spacing.xl,
    padding: theme.spacing.xl,
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
  form: {
    gap: theme.spacing.lg,
  },
  success: {
    color: theme.colors.success,
    fontSize: theme.typography.sizes.sm,
    fontWeight: theme.typography.weights.semibold,
  },
  actions: {
    gap: theme.spacing.md,
    paddingBottom: theme.spacing.xl,
  },
});
