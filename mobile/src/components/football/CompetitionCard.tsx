import { Pressable, StyleSheet, View } from 'react-native';
import { Ionicons } from '@expo/vector-icons';

import { theme } from '../../config/theme';
import { footballCompetitionVisuals } from '../../config/footballVisuals';
import { FootballCompetition } from '../../types/football.types';
import { AppText } from '../ui/AppText';
import { FootballCard } from './FootballCard';

type CompetitionCardProps = {
  competition: FootballCompetition;
  onPress: () => void;
};

export function CompetitionCard({ competition, onPress }: CompetitionCardProps) {
  const visual = footballCompetitionVisuals[competition.code];
  const accentColor = visual.accentColor;

  return (
    <Pressable
      accessibilityRole="button"
      onPress={onPress}
      style={({ pressed }) => [pressed && styles.pressed]}
    >
      <FootballCard style={styles.card}>
        <View style={[styles.sideLine, { backgroundColor: accentColor }]} />

        <View style={[styles.badge, { backgroundColor: withAlpha(accentColor, 0.12) }]}>
          <AppText style={styles.flag}>{visual.flag}</AppText>
          <AppText style={[styles.countryCode, { color: accentColor }]}>{visual.countryCode}</AppText>
        </View>

        <View style={styles.content}>
          <View style={styles.titleLine}>
            <AppText style={styles.inlineFlag}>{visual.flag}</AppText>
            <AppText style={styles.name}>{competition.name}</AppText>
          </View>
          <AppText style={styles.country}>{competition.country}</AppText>
          <AppText style={styles.meta}>{competition.code} · Résultats · Classement · Buteurs</AppText>
        </View>

        <Ionicons color={theme.colors.football.neon} name="chevron-forward" size={24} />
      </FootballCard>
    </Pressable>
  );
}

function withAlpha(hexColor: string, alpha: number): string {
  const hex = hexColor.replace('#', '');
  const red = parseInt(hex.substring(0, 2), 16);
  const green = parseInt(hex.substring(2, 4), 16);
  const blue = parseInt(hex.substring(4, 6), 16);

  return `rgba(${red}, ${green}, ${blue}, ${alpha})`;
}

const styles = StyleSheet.create({
  card: {
    alignItems: 'center',
    flexDirection: 'row',
    gap: theme.spacing.md,
    overflow: 'hidden',
  },
  sideLine: {
    bottom: 0,
    left: 0,
    position: 'absolute',
    top: 0,
    width: 4,
  },
  pressed: {
    opacity: 0.82,
  },
  badge: {
    alignItems: 'center',
    borderRadius: theme.radius.lg,
    gap: 2,
    height: 72,
    justifyContent: 'center',
    width: 72,
  },
  flag: {
    fontSize: 34,
    includeFontPadding: false,
    lineHeight: 42,
  },
  countryCode: {
    fontSize: theme.typography.sizes.xs,
    fontWeight: theme.typography.weights.bold,
    includeFontPadding: false,
    lineHeight: 14,
  },
  content: {
    flex: 1,
    gap: theme.spacing.xs,
    minWidth: 0,
  },
  titleLine: {
    alignItems: 'center',
    flexDirection: 'row',
    gap: theme.spacing.sm,
    minWidth: 0,
  },
  inlineFlag: {
    fontSize: 22,
    includeFontPadding: false,
    lineHeight: 28,
  },
  country: {
    color: theme.colors.text.secondary,
    fontSize: theme.typography.sizes.sm,
    lineHeight: 20,
  },
  name: {
    flex: 1,
    fontSize: theme.typography.sizes.lg,
    fontWeight: theme.typography.weights.bold,
    lineHeight: 24,
  },
  meta: {
    color: theme.colors.football.muted,
    fontSize: theme.typography.sizes.sm,
    lineHeight: 20,
  },
});
