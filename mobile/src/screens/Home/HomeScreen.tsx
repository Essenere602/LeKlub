import { StyleSheet, View } from 'react-native';
import { NativeStackScreenProps } from '@react-navigation/native-stack';

import { AppButton } from '../../components/ui/AppButton';
import { AppText } from '../../components/ui/AppText';
import { Screen } from '../../components/ui/Screen';
import { theme } from '../../config/theme';
import { config } from '../../config/env';
import { useAuth } from '../../hooks/useAuth';
import { MainStackParamList } from '../../navigation/navigation.types';

type HomeScreenProps = NativeStackScreenProps<MainStackParamList, 'Home'>;

export function HomeScreen({ navigation }: HomeScreenProps) {
  const { logout, user } = useAuth();
  const displayName = user?.profile.displayName ?? user?.username;

  return (
    <Screen>
      <View style={styles.header}>
        <AppText style={styles.kicker}>Session active</AppText>
        <AppText variant="title">Bienvenue {displayName}</AppText>
        <AppText variant="subtitle">
          L'authentification mobile est connectée au backend. Le Feed, le Football et les Messages privés sont disponibles.
        </AppText>
      </View>

      <View style={styles.panel}>
        <AppText variant="label">Utilisateur</AppText>
        <AppText>{user?.email}</AppText>
        <AppText variant="muted">Rôles : {user?.roles.join(', ')}</AppText>
        {user?.profile.favoriteTeamName ? (
          <AppText variant="muted">Équipe favorite : {user.profile.favoriteTeamName}</AppText>
        ) : null}
      </View>

      <View style={styles.panel}>
        <AppText variant="label">API</AppText>
        <AppText variant="muted">{config.apiBaseUrl}</AppText>
      </View>

      <View style={styles.footer}>
        <AppButton label="Feed" onPress={() => navigation.navigate('Feed')} />
        <AppButton label="Football" onPress={() => navigation.navigate('Football')} />
        <AppButton label="Messages" onPress={() => navigation.navigate('Conversations')} />
        <AppButton label="Mon profil" onPress={() => navigation.navigate('Profile')} />
        <AppButton label="Se déconnecter" onPress={logout} variant="secondary" />
      </View>
    </Screen>
  );
}

const styles = StyleSheet.create({
  header: {
    gap: theme.spacing.sm,
  },
  kicker: {
    color: theme.colors.accent,
    fontSize: theme.typography.sizes.sm,
    fontWeight: theme.typography.weights.bold,
    textTransform: 'uppercase',
  },
  panel: {
    backgroundColor: theme.colors.surface,
    borderColor: theme.colors.border,
    borderRadius: theme.radius.md,
    borderWidth: 1,
    gap: theme.spacing.sm,
    padding: theme.spacing.lg,
  },
  footer: {
    gap: theme.spacing.md,
    marginTop: 'auto',
  },
});
