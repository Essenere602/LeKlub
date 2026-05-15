import { StyleSheet, View } from 'react-native';
import { Ionicons } from '@expo/vector-icons';

import { theme } from '../../config/theme';
import { FootballMatch } from '../../types/football.types';
import { AppText } from '../ui/AppText';
import { FootballBadge } from './FootballBadge';
import { FootballCard } from './FootballCard';
import { FootballTeamLogo } from './FootballTeamLogo';

type MatchCardProps = {
  match: FootballMatch;
  mode: 'results' | 'upcoming';
};

export function MatchCard({ match, mode }: MatchCardProps) {
  const homeName = match.homeTeam.shortName ?? match.homeTeam.name ?? 'Équipe domicile';
  const awayName = match.awayTeam.shortName ?? match.awayTeam.name ?? 'Équipe extérieur';
  const hasScore = match.score.home !== null && match.score.away !== null;
  const statusLabel = labelForStatus(match.status);

  return (
    <FootballCard style={styles.card}>
      <View style={styles.matchHeader}>
        <AppText style={styles.matchdayLabel}>{match.matchday ? `Journée ${match.matchday}` : 'Match'}</AppText>
        {statusLabel ? <FootballBadge label={statusLabel} tone={toneForStatus(match.status)} /> : null}
      </View>

      <View style={styles.dateLine}>
        <Ionicons color={theme.colors.text.muted} name="time-outline" size={14} />
        <AppText style={styles.date}>{formatDate(match.utcDate)}</AppText>
      </View>

      <View style={styles.matchContent}>
        <View style={styles.teamContainer}>
          <FootballTeamLogo fallback={homeName} size={38} uri={match.homeTeam.crest} />
          <AppText adjustsFontSizeToFit minimumFontScale={0.82} numberOfLines={2} style={styles.teamName}>{homeName}</AppText>
        </View>

        <View style={styles.scoreContainer}>
          {mode === 'results' && hasScore ? (
            <>
              <AppText style={styles.score}>{match.score.home}</AppText>
              <AppText style={styles.scoreSeparator}>:</AppText>
              <AppText style={styles.score}>{match.score.away}</AppText>
            </>
          ) : (
            <View style={styles.vsContainer}>
              <Ionicons color={theme.colors.football.neon} name="trophy-outline" size={20} />
              <AppText style={styles.vsText}>VS</AppText>
            </View>
          )}
        </View>

        <View style={styles.teamContainer}>
          <FootballTeamLogo fallback={awayName} size={38} uri={match.awayTeam.crest} />
          <AppText adjustsFontSizeToFit minimumFontScale={0.82} numberOfLines={2} style={[styles.teamName, styles.awayTeam]}>{awayName}</AppText>
        </View>
      </View>
    </FootballCard>
  );
}

function formatDate(value: string | null): string {
  if (!value) {
    return 'Date non communiquée';
  }

  const date = new Date(value);

  if (Number.isNaN(date.getTime())) {
    return value;
  }

  return date.toLocaleDateString('fr-FR', {
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
    month: 'short',
    weekday: 'short',
  });
}

function labelForStatus(status: string | null): string | null {
  if (!status) {
    return null;
  }

  const labels: Record<string, string> = {
    FINISHED: 'Terminé',
    IN_PLAY: 'En cours',
    LIVE: 'Live',
    PAUSED: 'Pause',
    POSTPONED: 'Reporté',
    SCHEDULED: 'Programmé',
    SUSPENDED: 'Suspendu',
  };

  return labels[status] ?? status;
}

function toneForStatus(status: string | null): 'accent' | 'neutral' | 'success' | 'warning' | 'danger' {
  if (status === 'FINISHED') {
    return 'success';
  }

  if (status === 'LIVE' || status === 'IN_PLAY') {
    return 'danger';
  }

  if (status === 'POSTPONED' || status === 'SUSPENDED') {
    return 'warning';
  }

  if (status === 'SCHEDULED') {
    return 'accent';
  }

  return 'neutral';
}

const styles = StyleSheet.create({
  card: {
    gap: theme.spacing.sm,
  },
  matchHeader: {
    alignItems: 'center',
    flexDirection: 'row',
    justifyContent: 'space-between',
  },
  matchdayLabel: {
    color: theme.colors.text.secondary,
    fontSize: theme.typography.sizes.xs,
    fontWeight: theme.typography.weights.semibold,
    textTransform: 'uppercase',
  },
  dateLine: {
    alignItems: 'center',
    flexDirection: 'row',
    gap: theme.spacing.xs,
  },
  date: {
    color: theme.colors.text.muted,
    fontSize: theme.typography.sizes.xs,
  },
  matchContent: {
    alignItems: 'center',
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginTop: theme.spacing.sm,
    minHeight: 72,
  },
  teamContainer: {
    alignItems: 'center',
    flex: 1,
    gap: theme.spacing.sm,
    minWidth: 0,
  },
  teamName: {
    color: theme.colors.text.primary,
    fontSize: theme.typography.sizes.md,
    fontWeight: theme.typography.weights.semibold,
    lineHeight: 22,
    minHeight: 44,
    textAlign: 'center',
  },
  awayTeam: {
    textAlign: 'center',
  },
  scoreContainer: {
    alignItems: 'center',
    flexDirection: 'row',
    justifyContent: 'center',
    minWidth: 104,
    paddingHorizontal: theme.spacing.sm,
  },
  score: {
    color: theme.colors.football.neon,
    fontSize: 32,
    fontWeight: theme.typography.weights.bold,
    includeFontPadding: false,
    lineHeight: 38,
    minWidth: 38,
    textAlign: 'center',
  },
  scoreSeparator: {
    color: theme.colors.text.muted,
    fontSize: theme.typography.sizes['2xl'],
    fontWeight: theme.typography.weights.bold,
    includeFontPadding: false,
    lineHeight: 34,
    marginHorizontal: theme.spacing.xs,
  },
  vsContainer: {
    alignItems: 'center',
    gap: theme.spacing.xs,
  },
  vsText: {
    color: theme.colors.text.muted,
    fontWeight: theme.typography.weights.bold,
  },
});
