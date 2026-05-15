import { StyleSheet, View } from 'react-native';

import { theme } from '../../config/theme';
import { FootballScorer } from '../../types/football.types';
import { AppText } from '../ui/AppText';
import { FootballBadge } from './FootballBadge';
import { FootballCard } from './FootballCard';
import { FootballTeamLogo } from './FootballTeamLogo';

type ScorerRowProps = {
  index: number;
  scorer: FootballScorer;
};

export function ScorerRow({ index, scorer }: ScorerRowProps) {
  const playerName = scorer.player.name ?? 'Joueur';
  const teamName = scorer.team.shortName ?? scorer.team.name ?? 'Équipe';
  const rank = index + 1;
  const medal = medalForRank(rank);
  const topThree = rank <= 3;

  return (
    <FootballCard style={[styles.row, topThree && styles.topRow]}>
      <View style={styles.rankContainer}>
        {medal ? (
          <View style={styles.medalBadge}>
            <AppText style={styles.medal}>{medal}</AppText>
          </View>
        ) : (
          <View style={styles.rankBadge}>
            <AppText style={styles.rankNumber}>{rank}</AppText>
          </View>
        )}
      </View>
      <View style={styles.player}>
        <View style={styles.playerHeader}>
          <FootballTeamLogo fallback={teamName} size={34} uri={scorer.team.crest} />
          <View style={styles.playerIdentity}>
            <AppText numberOfLines={1} style={styles.playerName}>{playerName}</AppText>
            <AppText numberOfLines={1} style={styles.playerMeta}>
              {teamName}{scorer.player.nationality ? ` · ${scorer.player.nationality}` : ''}
            </AppText>
          </View>
        </View>
        <View style={styles.badges}>
          <FootballBadge icon="football" label={`${scorer.goals} buts`} tone="accent" />
          {scorer.assists !== null && scorer.assists > 0 ? <FootballBadge icon="arrow-forward" label={`${scorer.assists} passes`} /> : null}
          {scorer.penalties !== null && scorer.penalties > 0 ? <FootballBadge icon="flash-outline" label={`${scorer.penalties} pen.`} /> : null}
        </View>
      </View>
    </FootballCard>
  );
}

function medalForRank(rank: number): string | null {
  if (rank === 1) {
    return '1';
  }

  if (rank === 2) {
    return '2';
  }

  if (rank === 3) {
    return '3';
  }

  return null;
}

const styles = StyleSheet.create({
  row: {
    flexDirection: 'row',
    gap: theme.spacing.md,
  },
  topRow: {
    borderLeftColor: theme.colors.football.neon,
    borderLeftWidth: 4,
  },
  rankContainer: {
    alignItems: 'center',
    justifyContent: 'center',
    width: 52,
  },
  medalBadge: {
    alignItems: 'center',
    backgroundColor: theme.colors.football.neonSoft,
    borderColor: theme.colors.football.neon,
    borderRadius: 24,
    borderWidth: 1,
    height: 48,
    justifyContent: 'center',
    width: 48,
  },
  medal: {
    color: theme.colors.football.neon,
    fontSize: 24,
    fontWeight: theme.typography.weights.bold,
    includeFontPadding: false,
    lineHeight: 30,
  },
  rankBadge: {
    alignItems: 'center',
    backgroundColor: theme.colors.football.surface,
    borderRadius: 20,
    height: 40,
    justifyContent: 'center',
    width: 40,
  },
  rankNumber: {
    color: theme.colors.text.secondary,
    fontSize: theme.typography.sizes.lg,
    fontWeight: theme.typography.weights.bold,
    includeFontPadding: false,
    lineHeight: 24,
  },
  player: {
    flex: 1,
    gap: theme.spacing.sm,
  },
  playerHeader: {
    alignItems: 'center',
    flexDirection: 'row',
    gap: theme.spacing.sm,
  },
  playerIdentity: {
    flex: 1,
    minWidth: 0,
  },
  playerName: {
    color: theme.colors.text.primary,
    fontSize: theme.typography.sizes.lg,
    fontWeight: theme.typography.weights.semibold,
    lineHeight: 24,
  },
  playerMeta: {
    color: theme.colors.text.secondary,
    fontSize: theme.typography.sizes.sm,
    fontStyle: 'italic',
    lineHeight: 20,
  },
  badges: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: theme.spacing.xs,
  },
});
