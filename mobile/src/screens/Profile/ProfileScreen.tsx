import { useMemo, useState } from 'react';
import { Image, KeyboardAvoidingView, Platform, ScrollView, StyleSheet, View } from 'react-native';
import { BottomTabScreenProps } from '@react-navigation/bottom-tabs';

import { AppButton } from '../../components/ui/AppButton';
import { AppInput } from '../../components/ui/AppInput';
import { AppText } from '../../components/ui/AppText';
import { ErrorMessage } from '../../components/ui/ErrorMessage';
import { Screen } from '../../components/ui/Screen';
import { theme } from '../../config/theme';
import { useAuth } from '../../hooks/useAuth';
import { MainTabParamList } from '../../navigation/navigation.types';
import { toApiError } from '../../services/api/apiError';
import { profileService } from '../../services/user/profileService';
import { UpdateProfilePayload } from '../../types/user.types';

type ProfileScreenProps = BottomTabScreenProps<MainTabParamList, 'ProfileTab'>;

type ProfileForm = {
  displayName: string;
  bio: string;
  favoriteTeamName: string;
  avatarUrl: string;
};

export function ProfileScreen({ navigation }: ProfileScreenProps) {
  const { logout, refreshCurrentUser, user } = useAuth();
  const initialForm = useMemo<ProfileForm>(() => ({
    displayName: user?.profile.displayName ?? '',
    bio: user?.profile.bio ?? '',
    favoriteTeamName: user?.profile.favoriteTeamName ?? '',
    avatarUrl: user?.profile.avatarUrl ?? '',
  }), [user]);

  const [form, setForm] = useState<ProfileForm>(initialForm);
  const [error, setError] = useState<string | null>(null);
  const [successMessage, setSuccessMessage] = useState<string | null>(null);
  const [isSaving, setIsSaving] = useState(false);

  const trimmedAvatarUrl = form.avatarUrl.trim();

  function updateField(field: keyof ProfileForm, value: string) {
    setForm((currentForm) => ({ ...currentForm, [field]: value }));
    setError(null);
    setSuccessMessage(null);
  }

  async function submitProfile() {
    setIsSaving(true);
    setError(null);
    setSuccessMessage(null);

    const payload: UpdateProfilePayload = {
      displayName: nullableTrim(form.displayName),
      bio: nullableTrim(form.bio),
      favoriteTeamName: nullableTrim(form.favoriteTeamName),
      avatarUrl: nullableTrim(form.avatarUrl),
    };

    try {
      await profileService.updateCurrentProfile(payload);
      await refreshCurrentUser();
      setSuccessMessage('Profil mis à jour.');
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
            <AppText style={styles.kicker}>Profil LeKlub</AppText>
            <AppText variant="title">Mon profil</AppText>
            <AppText variant="subtitle">
              Ces informations seront visibles dans les prochains écrans sociaux de l'application.
            </AppText>
          </View>

          <View style={styles.identityPanel}>
            {trimmedAvatarUrl ? (
              <Image source={{ uri: trimmedAvatarUrl }} style={styles.avatar} />
            ) : (
              <View style={styles.avatarFallback}>
                <AppText style={styles.avatarInitial}>{(user?.username ?? 'L').slice(0, 1).toUpperCase()}</AppText>
              </View>
            )}

            <View style={styles.identityText}>
              <AppText variant="label">{user?.username}</AppText>
              <AppText variant="muted">{user?.email}</AppText>
              <AppText variant="muted">Rôles : {user?.roles.join(', ')}</AppText>
            </View>
          </View>

          <View style={styles.form}>
            <AppInput
              autoCapitalize="words"
              label="Nom affiché"
              maxLength={80}
              onChangeText={(value) => updateField('displayName', value)}
              placeholder="Ex. Samuel"
              value={form.displayName}
            />

            <AppInput
              autoCapitalize="sentences"
              label="Bio"
              maxLength={500}
              multiline
              onChangeText={(value) => updateField('bio', value)}
              placeholder="Quelques mots sur toi"
              style={styles.textArea}
              textAlignVertical="top"
              value={form.bio}
            />

            <AppInput
              autoCapitalize="words"
              label="Équipe favorite"
              maxLength={100}
              onChangeText={(value) => updateField('favoriteTeamName', value)}
              placeholder="Ex. Paris Saint-Germain"
              value={form.favoriteTeamName}
            />

            <AppInput
              autoCapitalize="none"
              keyboardType="url"
              label="URL avatar"
              maxLength={255}
              onChangeText={(value) => updateField('avatarUrl', value)}
              placeholder="https://..."
              value={form.avatarUrl}
            />
          </View>

          <ErrorMessage message={error} />
          {successMessage ? <AppText style={styles.success}>{successMessage}</AppText> : null}

          <View style={styles.actions}>
            <AppButton label="Enregistrer" loading={isSaving} onPress={submitProfile} />
            <AppButton label="Retour accueil" onPress={() => navigation.navigate('Home')} variant="secondary" />
            <AppButton label="Se déconnecter" onPress={logout} variant="ghost" />
          </View>
        </ScrollView>
      </KeyboardAvoidingView>
    </Screen>
  );
}

function nullableTrim(value: string): string | null {
  const trimmedValue = value.trim();

  return trimmedValue === '' ? null : trimmedValue;
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
  identityPanel: {
    alignItems: 'center',
    backgroundColor: theme.colors.surface,
    borderColor: theme.colors.border,
    borderRadius: theme.radius.md,
    borderWidth: 1,
    flexDirection: 'row',
    gap: theme.spacing.md,
    padding: theme.spacing.lg,
  },
  avatar: {
    backgroundColor: theme.colors.surfaceElevated,
    borderRadius: 28,
    height: 56,
    width: 56,
  },
  avatarFallback: {
    alignItems: 'center',
    backgroundColor: theme.colors.accentSoft,
    borderColor: theme.colors.accent,
    borderRadius: 28,
    borderWidth: 1,
    height: 56,
    justifyContent: 'center',
    width: 56,
  },
  avatarInitial: {
    color: theme.colors.accent,
    fontSize: theme.typography.sizes.xl,
    fontWeight: theme.typography.weights.bold,
  },
  identityText: {
    flex: 1,
    gap: theme.spacing.xs,
  },
  form: {
    gap: theme.spacing.lg,
  },
  textArea: {
    minHeight: 112,
    paddingTop: theme.spacing.md,
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
