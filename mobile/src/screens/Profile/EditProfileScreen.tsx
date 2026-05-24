import { useMemo, useState } from 'react';
import { Image, KeyboardAvoidingView, Platform, ScrollView, StyleSheet, View } from 'react-native';
import * as ImagePicker from 'expo-image-picker';
import { NativeStackScreenProps } from '@react-navigation/native-stack';

import { AppButton } from '../../components/ui/AppButton';
import { AppCard } from '../../components/ui/AppCard';
import { AppHeader } from '../../components/ui/AppHeader';
import { AppInput } from '../../components/ui/AppInput';
import { AppText } from '../../components/ui/AppText';
import { ErrorMessage } from '../../components/ui/ErrorMessage';
import { StatusBadge } from '../../components/ui/StatusBadge';
import { Screen } from '../../components/ui/Screen';
import { theme } from '../../config/theme';
import { useAuth } from '../../hooks/useAuth';
import { ProfileStackParamList } from '../../navigation/navigation.types';
import { toApiError } from '../../services/api/apiError';
import { profileService } from '../../services/user/profileService';
import { UpdateProfilePayload } from '../../types/user.types';

type EditProfileScreenProps = NativeStackScreenProps<ProfileStackParamList, 'EditProfile'>;

type ProfileForm = {
  displayName: string;
  bio: string;
  favoriteTeamName: string;
};

const MAX_AVATAR_SIZE_BYTES = 2 * 1024 * 1024;
const ALLOWED_AVATAR_MIMES = ['image/jpeg', 'image/png', 'image/webp'];

export function EditProfileScreen({ navigation }: EditProfileScreenProps) {
  const { refreshCurrentUser, user } = useAuth();
  const initialForm = useMemo<ProfileForm>(() => ({
    displayName: user?.profile.displayName ?? '',
    bio: user?.profile.bio ?? '',
    favoriteTeamName: user?.profile.favoriteTeamName ?? '',
  }), [user]);

  const [form, setForm] = useState<ProfileForm>(initialForm);
  const [error, setError] = useState<string | null>(null);
  const [successMessage, setSuccessMessage] = useState<string | null>(null);
  const [isSaving, setIsSaving] = useState(false);
  const [isUploadingAvatar, setIsUploadingAvatar] = useState(false);

  const avatarUrl = user?.profile.avatarUrl?.trim();

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

  async function chooseAvatar() {
    setError(null);
    setSuccessMessage(null);

    const permission = await ImagePicker.requestMediaLibraryPermissionsAsync();

    if (!permission.granted) {
      setError('Autorise l’accès à la galerie pour choisir une photo.');
      return;
    }

    const result = await ImagePicker.launchImageLibraryAsync({
      allowsEditing: true,
      aspect: [1, 1],
      mediaTypes: ['images'],
      quality: 0.85,
    });

    if (result.canceled || !result.assets[0]) {
      return;
    }

    const asset = result.assets[0];
    const mimeType = mimeTypeFor(asset);

    if (!mimeType || !ALLOWED_AVATAR_MIMES.includes(mimeType)) {
      setError('Choisis une image JPG, PNG ou WEBP.');
      return;
    }

    if (asset.fileSize !== undefined && asset.fileSize > MAX_AVATAR_SIZE_BYTES) {
      setError('La photo doit faire 2 Mo maximum.');
      return;
    }

    setIsUploadingAvatar(true);

    try {
      await profileService.uploadAvatar({
        uri: asset.uri,
        name: fileNameFor(asset, mimeType),
        type: mimeType,
      });
      await refreshCurrentUser();
      setSuccessMessage('Photo de profil mise à jour.');
    } catch (caughtError) {
      setError(toApiError(caughtError).message);
    } finally {
      setIsUploadingAvatar(false);
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
            kicker="Profil"
            subtitle="Ces informations alimentent ton identité dans LeKlub."
            title="Modifier mon profil"
          />

          <AppCard style={styles.identityPanel} variant="accent">
            {avatarUrl ? (
              <Image source={{ uri: avatarUrl }} style={styles.avatar} />
            ) : (
              <View style={styles.avatarFallback}>
                <AppText style={styles.avatarInitial}>{(user?.username ?? 'L').slice(0, 1).toUpperCase()}</AppText>
              </View>
            )}

            <View style={styles.identityText}>
              <AppText variant="label">{user?.username}</AppText>
              <AppText variant="muted">Photo JPG, PNG ou WEBP. Taille maximale : 2 Mo.</AppText>
            </View>
            <AppButton
              label="Changer"
              loading={isUploadingAvatar}
              onPress={chooseAvatar}
              style={styles.avatarButton}
              variant="secondary"
            />
          </AppCard>

          <AppCard style={styles.form}>
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
          </AppCard>

          <ErrorMessage message={error} />
          {successMessage ? (
            <View style={styles.successBox}>
              <StatusBadge label="Succès" variant="success" />
              <AppText style={styles.success}>{successMessage}</AppText>
            </View>
          ) : null}

          <View style={styles.actions}>
            <AppButton label="Enregistrer" loading={isSaving} onPress={submitProfile} />
            <AppButton label="Retour au compte" onPress={() => navigation.goBack()} variant="secondary" />
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

function mimeTypeFor(asset: ImagePicker.ImagePickerAsset): string | null {
  if (asset.mimeType) {
    return asset.mimeType;
  }

  const extension = extensionFor(asset.fileName ?? asset.uri);

  if (extension === 'jpg' || extension === 'jpeg') {
    return 'image/jpeg';
  }

  if (extension === 'png') {
    return 'image/png';
  }

  if (extension === 'webp') {
    return 'image/webp';
  }

  return null;
}

function fileNameFor(asset: ImagePicker.ImagePickerAsset, mimeType: string): string {
  const fallbackExtension = mimeType === 'image/png' ? 'png' : mimeType === 'image/webp' ? 'webp' : 'jpg';
  const rawName = asset.fileName?.trim();

  if (rawName) {
    return rawName;
  }

  return `avatar.${fallbackExtension}`;
}

function extensionFor(value: string): string {
  const cleanValue = value.split('?')[0] ?? value;
  const extension = cleanValue.split('.').pop();

  return extension?.toLowerCase() ?? '';
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
  identityPanel: {
    alignItems: 'center',
    flexDirection: 'row',
    gap: theme.spacing.md,
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
    includeFontPadding: false,
    lineHeight: 26,
  },
  identityText: {
    flex: 1,
    gap: theme.spacing.xs,
  },
  avatarButton: {
    minHeight: 42,
    paddingHorizontal: theme.spacing.md,
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
  successBox: {
    alignItems: 'center',
    backgroundColor: 'rgba(43, 212, 134, 0.1)',
    borderColor: theme.colors.success,
    borderRadius: theme.radius.md,
    borderWidth: 1,
    flexDirection: 'row',
    gap: theme.spacing.sm,
    padding: theme.spacing.md,
  },
  actions: {
    gap: theme.spacing.md,
    paddingBottom: theme.spacing.xl,
  },
});
