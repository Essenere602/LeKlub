import { useMemo, useState } from 'react';
import { KeyboardAvoidingView, Platform, ScrollView, StyleSheet, View } from 'react-native';
import { NativeStackScreenProps } from '@react-navigation/native-stack';

import { AppBadge } from '../../components/ui/AppBadge';
import { AppButton } from '../../components/ui/AppButton';
import { AppCard } from '../../components/ui/AppCard';
import { AppHeader } from '../../components/ui/AppHeader';
import { AppInput } from '../../components/ui/AppInput';
import { AppText } from '../../components/ui/AppText';
import { ErrorMessage } from '../../components/ui/ErrorMessage';
import { Screen } from '../../components/ui/Screen';
import { theme } from '../../config/theme';
import { useAuth } from '../../hooks/useAuth';
import { ProfileStackParamList } from '../../navigation/navigation.types';
import { toApiError } from '../../services/api/apiError';
import { profileService } from '../../services/user/profileService';

type EditAccountScreenProps = NativeStackScreenProps<ProfileStackParamList, 'EditAccount'>;

type AccountForm = {
  email: string;
  username: string;
  currentPassword: string;
};

export function EditAccountScreen({ navigation }: EditAccountScreenProps) {
  const { refreshCurrentUser, user } = useAuth();
  const initialForm = useMemo<AccountForm>(() => ({
    email: user?.email ?? '',
    username: user?.username ?? '',
    currentPassword: '',
  }), [user]);

  const [form, setForm] = useState<AccountForm>(initialForm);
  const [error, setError] = useState<string | null>(null);
  const [successMessage, setSuccessMessage] = useState<string | null>(null);
  const [isSaving, setIsSaving] = useState(false);

  const emailChanged = form.email.trim().toLowerCase() !== (user?.email ?? '').toLowerCase();

  function updateField(field: keyof AccountForm, value: string) {
    setForm((currentForm) => ({ ...currentForm, [field]: value }));
    setError(null);
    setSuccessMessage(null);
  }

  async function submitAccount() {
    if (emailChanged && form.currentPassword.length === 0) {
      setError("Le mot de passe actuel est obligatoire pour modifier l'email.");
      return;
    }

    setIsSaving(true);
    setError(null);
    setSuccessMessage(null);

    try {
      await profileService.updateCurrentAccount({
        email: form.email.trim(),
        username: form.username.trim(),
        currentPassword: emailChanged ? form.currentPassword : undefined,
      });
      setSuccessMessage(emailChanged
        ? 'Compte mis à jour. Reconnexion nécessaire après changement email.'
        : 'Compte mis à jour.');
      await refreshCurrentUser();
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
          <AppHeader
            kicker="Compte"
            subtitle="Modifie ton email de connexion et ton nom utilisateur public."
            title="Modifier mon compte"
          />

          <AppCard style={styles.notice} variant="accent">
            <AppBadge label="Sécurité" tone="accent" />
            <AppText style={styles.noticeText}>
              L'email identifie aussi la session JWT. Après un changement email, une reconnexion peut être nécessaire.
            </AppText>
          </AppCard>

          <AppCard style={styles.form}>
            <AppInput
              autoCapitalize="none"
              keyboardType="email-address"
              label="Email"
              maxLength={180}
              onChangeText={(value) => updateField('email', value)}
              placeholder="email@exemple.test"
              value={form.email}
            />

            <AppInput
              autoCapitalize="none"
              label="Nom utilisateur"
              maxLength={50}
              onChangeText={(value) => updateField('username', value)}
              placeholder="nom_utilisateur"
              value={form.username}
            />

            {emailChanged ? (
              <AppInput
                label="Mot de passe actuel"
                onChangeText={(value) => updateField('currentPassword', value)}
                placeholder="Obligatoire pour changer l'email"
                secureTextEntry
                value={form.currentPassword}
              />
            ) : null}
          </AppCard>

          <ErrorMessage message={error} />
          {successMessage ? <AppText style={styles.success}>{successMessage}</AppText> : null}

          <View style={styles.actions}>
            <AppButton label="Enregistrer" loading={isSaving} onPress={submitAccount} />
            <AppButton label="Retour au compte" onPress={() => navigation.goBack()} variant="secondary" />
          </View>
        </ScrollView>
      </KeyboardAvoidingView>
    </Screen>
  );
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
  notice: {
    gap: theme.spacing.sm,
  },
  noticeText: {
    color: theme.colors.text.secondary,
    fontSize: theme.typography.sizes.sm,
    lineHeight: 20,
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
