import { StyleSheet, View } from 'react-native';
import { theme } from '../../config/theme';
import { FootballStanding } from '../../types/football.types';
import { AppText } from '../ui/AppText';
import { FootballTeamLogo } from './FootballTeamLogo';

type StandingRowProps = {
  standing: FootballStanding;
};

export function StandingRow({ standing }: StandingRowProps) {
  const teamName = standing.team.shortName ?? standing.team.name ?? 'Équipe';
  const positionTone = getPositionTone(standing.position);
  const isBottom = standing.position !== null && standing.position >= 18;

  return (
    <View style={styles.rowWrapper}>
      {positionTone ? <View style={[styles.positionIndicator, { backgroundColor: positionTone }]} /> : null}
      <View style={styles.row}>
        <View style={styles.positionContainer}>
          <AppText style={[
            styles.position,
            positionTone && { color: positionTone },
          ]}>
            {standing.position ?? '-'}
          </AppText>
        </View>
        <View style={styles.team}>
          <FootballTeamLogo fallback={teamName} size={26} uri={standing.team.crest} />
          <AppText numberOfLines={1} style={styles.teamName}>{teamName}</AppText>
        </View>
        <AppText style={styles.stat}>{standing.playedGames}</AppText>
        <AppText style={[styles.stat, styles.points]}>{standing.points}</AppText>
        <AppText style={[
          styles.stat,
          standing.goalDifference > 0 && styles.positiveDiff,
          (standing.goalDifference < 0 || isBottom) && styles.negativeDiff,
        ]}>
          {standing.goalDifference > 0 ? '+' : ''}{standing.goalDifference}
        </AppText>
      </View>
    </View>
  );
}

function getPositionTone(position: number | null): string | null {
  if (position === null) {
    return null;
  }

  if (position <= 4) {
    return theme.colors.success;
  }

  if (position >= 18) {
    return theme.colors.danger;
  }

  return null;
}

const styles = StyleSheet.create({
  rowWrapper: {
    position: 'relative',
  },
  positionIndicator: {
    bottom: 0,
    left: 0,
    position: 'absolute',
    top: 0,
    width: 4,
    zIndex: 1,
  },
  row: {
    alignItems: 'center',
    backgroundColor: theme.colors.football.surface,
    borderBottomColor: theme.colors.football.border,
    borderBottomWidth: 1,
    flexDirection: 'row',
    paddingHorizontal: theme.spacing.md,
    paddingVertical: theme.spacing.md,
  },
  positionContainer: {
    alignItems: 'center',
    width: 32,
  },
  position: {
    color: theme.colors.text.secondary,
    fontWeight: theme.typography.weights.bold,
  },
  team: {
    alignItems: 'center',
    flex: 1,
    flexDirection: 'row',
    gap: theme.spacing.sm,
    minWidth: 0,
    paddingHorizontal: theme.spacing.sm,
  },
  teamName: {
    color: theme.colors.text.primary,
    flex: 1,
    fontWeight: theme.typography.weights.semibold,
    lineHeight: 22,
  },
  stat: {
    color: theme.colors.text.secondary,
    fontSize: theme.typography.sizes.md,
    lineHeight: 22,
    textAlign: 'center',
    width: 44,
  },
  points: {
    color: theme.colors.football.neon,
    fontWeight: theme.typography.weights.bold,
  },
  positiveDiff: {
    color: theme.colors.football.neon,
    fontWeight: theme.typography.weights.semibold,
  },
  negativeDiff: {
    color: theme.colors.danger,
    fontWeight: theme.typography.weights.semibold,
  },
});
