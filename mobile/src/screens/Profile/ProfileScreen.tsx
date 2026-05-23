import { Image, ScrollView, StyleSheet, View } from 'react-native';
import { NativeStackScreenProps } from '@react-navigation/native-stack';

import { AppBadge } from '../../components/ui/AppBadge';
import { AppButton } from '../../components/ui/AppButton';
import { AppCard } from '../../components/ui/AppCard';
import { AppHeader } from '../../components/ui/AppHeader';
import { AppSection } from '../../components/ui/AppSection';
import { AppText } from '../../components/ui/AppText';
import { Screen } from '../../components/ui/Screen';
import { SettingsRow } from '../../components/ui/SettingsRow';
import { theme } from '../../config/theme';
import { useAuth } from '../../hooks/useAuth';
import { ProfileStackParamList } from '../../navigation/navigation.types';

type ProfileScreenProps = NativeStackScreenProps<ProfileStackParamList, 'Profile'>;

export function ProfileScreen({ navigation }: ProfileScreenProps) {
  const { logout, user } = useAuth();
  const profile = user?.profile;
  const displayName = profile?.displayName || user?.username || 'Membre LeKlub';
  const avatarUrl = profile?.avatarUrl?.trim();
  const isAdmin = user?.roles.includes('ROLE_ADMIN') ?? false;

  return (
    <Screen style={styles.screen}>
      <ScrollView contentContainerStyle={styles.content}>
        <AppHeader
          kicker="Compte"
          subtitle="Gère ton identité, ta sécurité et ta session LeKlub."
          title="Mon espace"
        />

        <AppCard style={styles.identityCard} variant="accent">
          <View style={styles.identityTop}>
            {avatarUrl ? (
              <Image source={{ uri: avatarUrl }} style={styles.avatar} />
            ) : (
              <View style={styles.avatarFallback}>
                <AppText style={styles.avatarInitial}>{displayName.slice(0, 1).toUpperCase()}</AppText>
              </View>
            )}

            <View style={styles.identityText}>
              <AppText style={styles.displayName}>{displayName}</AppText>
              <AppText variant="muted">@{user?.username}</AppText>
            </View>
          </View>

          <View style={styles.badges}>
            <AppBadge label={isAdmin ? 'Admin' : 'Membre'} tone={isAdmin ? 'accent' : 'neutral'} />
            {profile?.favoriteTeamName ? (
              <AppBadge label={profile.favoriteTeamName} tone="success" />
            ) : null}
          </View>
        </AppCard>

        <AppSection title="Profil">
          <SettingsRow
            onPress={() => navigation.navigate('EditProfile')}
            subtitle={profile?.bio || 'Complète ton nom affiché, ta bio et ton équipe favorite.'}
            title="Modifier mon profil"
          />
          <SettingsRow
            meta={profile?.favoriteTeamName || 'Non renseignée'}
            title="Équipe favorite"
          />
        </AppSection>

        <AppSection title="Sécurité">
          <SettingsRow
            onPress={() => navigation.navigate('Notifications')}
            subtitle="Suivi des décisions de modération et messages système."
            title="Notifications"
          />
          <SettingsRow
            onPress={() => navigation.navigate('ChangePassword')}
            subtitle="Ancien mot de passe, nouveau mot de passe et confirmation."
            title="Changer mon mot de passe"
          />
          <SettingsRow
            meta="Secure Store"
            subtitle="Le token de connexion est conservé dans le stockage sécurisé du téléphone."
            title="Session mobile"
          />
        </AppSection>

        <AppSection title="Informations compte">
          <SettingsRow meta={user?.email ?? '-'} title="Email" />
          <SettingsRow meta={formatRoles(user?.roles ?? [])} title="Rôle" />
          <SettingsRow meta={formatDate(user?.createdAt)} title="Créé le" />
          <SettingsRow
            subtitle="Cette version privilégie un compte simple et sécurisé. Le changement d'email et la suppression de compte viendront dans une étape dédiée si nécessaire."
            title="Version actuelle"
          />
        </AppSection>

        <View style={styles.actions}>
          <AppButton label="Se déconnecter" onPress={logout} variant="ghost" />
        </View>
      </ScrollView>
    </Screen>
  );
}

function formatRoles(roles: string[]): string {
  if (roles.includes('ROLE_ADMIN')) {
    return 'Admin';
  }

  return 'Membre';
}

function formatDate(value: string | undefined): string {
  if (!value) {
    return '-';
  }

  const date = new Date(value);

  if (Number.isNaN(date.getTime())) {
    return '-';
  }

  return new Intl.DateTimeFormat('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
  }).format(date);
}

const styles = StyleSheet.create({
  screen: {
    padding: 0,
  },
  content: {
    gap: theme.spacing.lg,
    padding: theme.spacing.xl,
    paddingBottom: theme.spacing['2xl'],
  },
  identityCard: {
    gap: theme.spacing.lg,
  },
  identityTop: {
    alignItems: 'center',
    flexDirection: 'row',
    gap: theme.spacing.md,
  },
  avatar: {
    backgroundColor: theme.colors.surfaceElevated,
    borderRadius: 34,
    height: 68,
    width: 68,
  },
  avatarFallback: {
    alignItems: 'center',
    backgroundColor: theme.colors.accentSoft,
    borderColor: theme.colors.accent,
    borderRadius: 34,
    borderWidth: 1,
    height: 68,
    justifyContent: 'center',
    width: 68,
  },
  avatarInitial: {
    color: theme.colors.accent,
    fontSize: theme.typography.sizes['2xl'],
    fontWeight: theme.typography.weights.bold,
  },
  identityText: {
    flex: 1,
    gap: theme.spacing.xs,
  },
  displayName: {
    fontSize: theme.typography.sizes.xl,
    fontWeight: theme.typography.weights.bold,
  },
  badges: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: theme.spacing.sm,
  },
  actions: {
    paddingTop: theme.spacing.sm,
  },
});
