import { Pressable, StyleSheet, View } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { BottomTabScreenProps } from '@react-navigation/bottom-tabs';

import { AppCard } from '../../components/ui/AppCard';
import { AppHeader } from '../../components/ui/AppHeader';
import { AppText } from '../../components/ui/AppText';
import { Screen } from '../../components/ui/Screen';
import { StatusBadge } from '../../components/ui/StatusBadge';
import { theme } from '../../config/theme';
import { useAuth } from '../../hooks/useAuth';
import { MainTabParamList } from '../../navigation/navigation.types';

type HomeScreenProps = BottomTabScreenProps<MainTabParamList, 'Home'>;

export function HomeScreen({ navigation }: HomeScreenProps) {
  const { logout, user } = useAuth();
  const displayName = user?.profile.displayName ?? user?.username ?? 'membre';
  const isAdmin = user?.roles.includes('ROLE_ADMIN') ?? false;

  return (
    <Screen>
      <AppHeader
        kicker="LeKlub"
        subtitle="Ton espace football social : feed, compétitions, messages privés et profil."
        title={`Bienvenue ${displayName}`}
      />

      <AppCard variant="accent" style={styles.identityCard}>
        <View style={styles.avatar}>
          <AppText style={styles.avatarText}>{displayName.slice(0, 1).toUpperCase()}</AppText>
        </View>
        <View style={styles.identityContent}>
          <View style={styles.identityTitle}>
            <AppText variant="label">@{user?.username}</AppText>
            {isAdmin ? <StatusBadge label="Admin" variant="accent" /> : null}
          </View>
          <AppText variant="muted">{user?.profile.bio ?? 'Membre du Klub'}</AppText>
        </View>
      </AppCard>

      {user?.profile.favoriteTeamName ? (
        <AppCard style={styles.favoriteCard}>
          <Ionicons color={theme.colors.accent} name="star-outline" size={22} />
          <View style={styles.favoriteContent}>
            <AppText variant="label">Équipe favorite</AppText>
            <AppText>{user.profile.favoriteTeamName}</AppText>
          </View>
        </AppCard>
      ) : null}

      <View style={styles.quickLinks}>
        <ShortcutCard
          icon="chatbubbles-outline"
          label="Feed"
          onPress={() => navigation.navigate('FeedTab')}
          text="Publier et commenter"
        />
        <ShortcutCard
          icon="football-outline"
          label="Football"
          onPress={() => navigation.navigate('FootballTab')}
          text="Résultats et classements"
        />
        <ShortcutCard
          icon="mail-outline"
          label="Messages"
          onPress={() => navigation.navigate('MessagingTab')}
          text="Conversations privées"
        />
        <ShortcutCard
          icon="person-outline"
          label="Profil"
          onPress={() => navigation.navigate('ProfileTab')}
          text="Informations publiques"
        />
      </View>

      {isAdmin ? (
        <AppCard style={styles.adminNotice}>
          <StatusBadge label="Admin actif" variant="warning" />
          <AppText variant="muted">
            L'onglet Admin est disponible pour superviser le MVP et modérer le Feed.
          </AppText>
        </AppCard>
      ) : null}

      <View style={styles.footer}>
        <Pressable accessibilityRole="button" onPress={logout} style={styles.logoutButton}>
          <Ionicons color={theme.colors.text.secondary} name="log-out-outline" size={18} />
          <AppText style={styles.logoutText}>Se déconnecter</AppText>
        </Pressable>
      </View>
    </Screen>
  );
}

type ShortcutCardProps = {
  icon: keyof typeof Ionicons.glyphMap;
  label: string;
  text: string;
  onPress: () => void;
};

function ShortcutCard({ icon, label, onPress, text }: ShortcutCardProps) {
  return (
    <Pressable
      accessibilityRole="button"
      onPress={onPress}
      style={({ pressed }) => [styles.shortcut, pressed && styles.pressed]}
    >
      <View style={styles.shortcutIcon}>
        <Ionicons color={theme.colors.accent} name={icon} size={22} />
      </View>
      <View style={styles.shortcutCopy}>
        <AppText variant="label">{label}</AppText>
        <AppText variant="muted">{text}</AppText>
      </View>
      <Ionicons color={theme.colors.text.muted} name="chevron-forward" size={18} />
    </Pressable>
  );
}

const styles = StyleSheet.create({
  identityCard: {
    alignItems: 'center',
    flexDirection: 'row',
    gap: theme.spacing.md,
  },
  avatar: {
    alignItems: 'center',
    backgroundColor: theme.colors.accentSoft,
    borderColor: theme.colors.accent,
    borderRadius: 28,
    borderWidth: 1,
    height: 56,
    justifyContent: 'center',
    width: 56,
  },
  avatarText: {
    color: theme.colors.accent,
    fontSize: theme.typography.sizes.xl,
    fontWeight: theme.typography.weights.bold,
  },
  identityContent: {
    flex: 1,
    gap: theme.spacing.sm,
  },
  identityTitle: {
    alignItems: 'center',
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: theme.spacing.sm,
  },
  favoriteCard: {
    alignItems: 'center',
    flexDirection: 'row',
    gap: theme.spacing.md,
  },
  favoriteContent: {
    flex: 1,
    gap: theme.spacing.xs,
  },
  quickLinks: {
    gap: theme.spacing.md,
  },
  shortcut: {
    alignItems: 'center',
    backgroundColor: theme.colors.surface,
    borderColor: theme.colors.border,
    borderRadius: theme.radius.lg,
    borderWidth: 1,
    flexDirection: 'row',
    gap: theme.spacing.md,
    padding: theme.spacing.lg,
  },
  pressed: {
    opacity: 0.82,
  },
  shortcutIcon: {
    alignItems: 'center',
    backgroundColor: theme.colors.accentSoft,
    borderRadius: theme.radius.md,
    height: 42,
    justifyContent: 'center',
    width: 42,
  },
  shortcutCopy: {
    flex: 1,
    gap: theme.spacing.xs,
  },
  adminNotice: {
    gap: theme.spacing.md,
  },
  footer: {
    marginTop: 'auto',
  },
  logoutButton: {
    alignItems: 'center',
    alignSelf: 'center',
    flexDirection: 'row',
    gap: theme.spacing.sm,
    padding: theme.spacing.md,
  },
  logoutText: {
    color: theme.colors.text.secondary,
    fontSize: theme.typography.sizes.sm,
    fontWeight: theme.typography.weights.semibold,
  },
});
