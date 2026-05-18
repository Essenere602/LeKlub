import { ComponentProps, useCallback, useEffect, useState } from 'react';
import { Pressable, ScrollView, StyleSheet, View } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { NativeStackScreenProps } from '@react-navigation/native-stack';

import { FootballCard } from '../../components/football/FootballCard';
import { FootballEmptyState } from '../../components/football/FootballEmptyState';
import { FootballSectionTabs, FootballTabItem } from '../../components/football/FootballSectionTabs';
import { FootballShell } from '../../components/football/FootballShell';
import { MatchCard } from '../../components/football/MatchCard';
import { ScorerRow } from '../../components/football/ScorerRow';
import { StandingRow } from '../../components/football/StandingRow';
import { AppButton } from '../../components/ui/AppButton';
import { AppText } from '../../components/ui/AppText';
import { Screen } from '../../components/ui/Screen';
import { theme } from '../../config/theme';
import { FootballStackParamList } from '../../navigation/navigation.types';
import { toApiError } from '../../services/api/apiError';
import { footballService } from '../../services/football/footballService';
import { FootballCompetitionCode, FootballMatch, FootballScorer, FootballStanding } from '../../types/football.types';

type FootballCompetitionScreenProps = NativeStackScreenProps<FootballStackParamList, 'FootballCompetition'>;

type FootballSection = 'results' | 'upcoming' | 'standings' | 'scorers';

const SECTIONS: Array<FootballTabItem<FootballSection>> = [
  { icon: 'checkmark-circle-outline', key: 'results', label: 'Résultats' },
  { icon: 'calendar-outline', key: 'upcoming', label: 'À venir' },
  { icon: 'list-outline', key: 'standings', label: 'Classement' },
  { icon: 'trophy-outline', key: 'scorers', label: 'Buteurs' },
];

const MATCHDAY_LIMIT = 20;

const MATCHDAY_COUNT_BY_COMPETITION: Record<FootballCompetitionCode, number> = {
  BL1: 34,
  FL1: 34,
  PD: 38,
  PL: 38,
  SA: 38,
};

export function FootballCompetitionScreen({ navigation, route }: FootballCompetitionScreenProps) {
  const { code, country, name } = route.params;
  const [section, setSection] = useState<FootballSection>('results');
  const [selectedMatchday, setSelectedMatchday] = useState(1);
  const [matches, setMatches] = useState<FootballMatch[]>([]);
  const [standings, setStandings] = useState<FootballStanding[]>([]);
  const [scorers, setScorers] = useState<FootballScorer[]>([]);
  const [error, setError] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(false);

  const loadSection = useCallback(async () => {
    setError(null);
    setIsLoading(true);

    try {
      if (section === 'results') {
        setMatches(await footballService.listResults(code, MATCHDAY_LIMIT, selectedMatchday));
        return;
      }

      if (section === 'upcoming') {
        setMatches(await footballService.listUpcomingMatches(code, MATCHDAY_LIMIT, selectedMatchday));
        return;
      }

      if (section === 'standings') {
        setStandings(await footballService.listStandings(code));
        return;
      }

      setScorers(await footballService.listScorers(code));
    } catch (caughtError) {
      setError(toApiError(caughtError).message);
    } finally {
      setIsLoading(false);
    }
  }, [code, section, selectedMatchday]);

  useEffect(() => {
    void loadSection();
  }, [loadSection]);

  return (
    <Screen style={styles.screen}>
      <FootballShell
        icon={iconForCountry(country)}
        subtitle={`${country} · Données normalisées par l'API LeKlub`}
        title={name}
      >
        <ScrollView contentContainerStyle={styles.content}>
          <AppButton label="Retour compétitions" onPress={() => navigation.goBack()} variant="secondary" />

          <FootballSectionTabs activeKey={section} items={SECTIONS} onChange={setSection} />

          {section === 'results' || section === 'upcoming' ? (
            <MatchdaySelector
              maxMatchday={MATCHDAY_COUNT_BY_COMPETITION[code]}
              selectedMatchday={selectedMatchday}
              onChange={setSelectedMatchday}
            />
          ) : null}

          {isLoading ? (
            <FootballEmptyState
              message="Récupération des données football depuis l'API LeKlub."
              title="Chargement"
              type="loading"
            />
          ) : null}

          {error ? (
            <FootballEmptyState
              icon="warning-outline"
              message={error}
              onRetry={loadSection}
              title="Donnée football indisponible"
              type="error"
            />
          ) : null}

          {!isLoading && !error ? (
            <View style={styles.sectionContent}>
              {section === 'results' ? <MatchesList matches={matches} mode="results" /> : null}
              {section === 'upcoming' ? <MatchesList matches={matches} mode="upcoming" /> : null}
              {section === 'standings' ? <StandingsList standings={standings} /> : null}
              {section === 'scorers' ? <ScorersList scorers={scorers} /> : null}
            </View>
          ) : null}
        </ScrollView>
      </FootballShell>
    </Screen>
  );
}

type MatchesListProps = {
  matches: FootballMatch[];
  mode: 'results' | 'upcoming';
};

function MatchesList({ matches, mode }: MatchesListProps) {
  if (matches.length === 0) {
    return <EmptyState icon="football-outline" message="La donnée peut dépendre du calendrier ou du quota API." title="Aucun match disponible" />;
  }

  return (
    <>
      {matches.map((match, index) => (
        <MatchCard key={match.id ?? `${match.utcDate}-${index}`} match={match} mode={mode} />
      ))}
    </>
  );
}

function StandingsList({ standings }: { standings: FootballStanding[] }) {
  if (standings.length === 0) {
    return <EmptyState icon="list-outline" message="Le classement apparaîtra dès que la compétition aura des données disponibles." title="Aucun classement disponible" />;
  }

  return (
    <FootballCard variant="table">
      <View style={styles.tableHeader}>
        <AppText style={[styles.tableHeaderText, styles.positionColumn]}>#</AppText>
        <AppText style={[styles.tableHeaderText, styles.teamColumn]}>Équipe</AppText>
        <AppText style={[styles.tableHeaderText, styles.statColumn]}>MJ</AppText>
        <AppText style={[styles.tableHeaderText, styles.statColumn]}>Pts</AppText>
        <AppText style={[styles.tableHeaderText, styles.statColumn]}>Diff</AppText>
      </View>
      {standings.map((standing, index) => (
        <StandingRow key={standing.team.id ?? `${standing.team.name}-${index}`} standing={standing} />
      ))}
    </FootballCard>
  );
}

function ScorersList({ scorers }: { scorers: FootballScorer[] }) {
  if (scorers.length === 0) {
    return <EmptyState icon="trophy-outline" message="Les buteurs apparaîtront dès que l'API externe en retourne pour ce championnat." title="Aucun buteur disponible" />;
  }

  return (
    <>
      <View style={styles.sectionHeader}>
        <AppText style={styles.sectionTitle}>Top buteurs</AppText>
        <AppText style={styles.sectionSubtitle}>{scorers.length} joueurs classés</AppText>
      </View>
      {scorers.map((scorer, index) => (
        <ScorerRow
          index={index}
          key={scorer.player.id ?? `${scorer.player.name}-${index}`}
          scorer={scorer}
        />
      ))}
    </>
  );
}

function EmptyState({ icon, message, title }: { icon: ComponentProps<typeof FootballEmptyState>['icon']; message: string; title: string }) {
  return (
    <FootballEmptyState icon={icon} message={message} title={title} />
  );
}

type MatchdaySelectorProps = {
  maxMatchday: number;
  selectedMatchday: number;
  onChange: (matchday: number) => void;
};

function MatchdaySelector({ maxMatchday, selectedMatchday, onChange }: MatchdaySelectorProps) {
  const canGoPrevious = selectedMatchday > 1;
  const canGoNext = selectedMatchday < maxMatchday;
  const matchdays = Array.from({ length: maxMatchday }, (_, index) => index + 1);

  return (
    <View style={styles.matchdaySelector}>
      <View style={styles.matchdayHeader}>
        <Pressable
          accessibilityLabel="Journée précédente"
          disabled={!canGoPrevious}
          onPress={() => onChange(selectedMatchday - 1)}
          style={[styles.matchdayButton, !canGoPrevious && styles.matchdayButtonDisabled]}
        >
          <Ionicons
            color={canGoPrevious ? theme.colors.football.neon : theme.colors.text.muted}
            name="chevron-back"
            size={22}
          />
        </Pressable>

        <View style={styles.matchdayCenter}>
          <AppText style={styles.matchdayEyebrow}>Sélection journée</AppText>
          <AppText style={styles.matchdayTitle}>Journée {selectedMatchday}</AppText>
          <AppText style={styles.matchdayMeta}>1 à {maxMatchday}</AppText>
        </View>

        <Pressable
          accessibilityLabel="Journée suivante"
          disabled={!canGoNext}
          onPress={() => onChange(selectedMatchday + 1)}
          style={[styles.matchdayButton, !canGoNext && styles.matchdayButtonDisabled]}
        >
          <Ionicons
            color={canGoNext ? theme.colors.football.neon : theme.colors.text.muted}
            name="chevron-forward"
            size={22}
          />
        </Pressable>
      </View>

      <ScrollView
        horizontal
        showsHorizontalScrollIndicator={false}
        style={styles.matchdayRail}
        contentContainerStyle={styles.matchdayRailContent}
      >
        {matchdays.map((matchday) => {
          const isActive = matchday === selectedMatchday;

          return (
            <Pressable
              accessibilityLabel={`Journée ${matchday}`}
              key={matchday}
              onPress={() => onChange(matchday)}
              style={[styles.matchdayChip, isActive && styles.matchdayChipActive]}
            >
              <AppText style={[styles.matchdayChipText, isActive && styles.matchdayChipTextActive]}>
                J{matchday}
              </AppText>
            </Pressable>
          );
        })}
      </ScrollView>
    </View>
  );
}

function iconForCountry(country: string): ComponentProps<typeof FootballShell>['icon'] {
  if (country === 'France') {
    return 'football';
  }

  return 'trophy';
}

const styles = StyleSheet.create({
  screen: {
    padding: 0,
  },
  content: {
    gap: theme.spacing.lg,
    padding: theme.spacing.lg,
    paddingTop: theme.spacing.xl,
  },
  sectionContent: {
    gap: theme.spacing.md,
  },
  matchdaySelector: {
    backgroundColor: theme.colors.football.black,
    borderColor: theme.colors.football.border,
    borderRadius: 22,
    borderWidth: 1,
    gap: theme.spacing.md,
    padding: theme.spacing.sm,
  },
  matchdayHeader: {
    alignItems: 'center',
    flexDirection: 'row',
    gap: theme.spacing.md,
  },
  matchdayButton: {
    alignItems: 'center',
    backgroundColor: theme.colors.football.surface,
    borderColor: theme.colors.football.neonSoft,
    borderRadius: 18,
    borderWidth: 1,
    height: 46,
    justifyContent: 'center',
    width: 46,
  },
  matchdayButtonDisabled: {
    opacity: 0.45,
  },
  matchdayCenter: {
    alignItems: 'center',
    flex: 1,
    gap: 2,
    minWidth: 0,
  },
  matchdayEyebrow: {
    color: theme.colors.football.neon,
    fontSize: theme.typography.sizes.xs,
    fontWeight: theme.typography.weights.bold,
    lineHeight: 16,
    textTransform: 'uppercase',
  },
  matchdayTitle: {
    color: theme.colors.text.primary,
    fontSize: theme.typography.sizes.lg,
    fontWeight: theme.typography.weights.bold,
    lineHeight: 24,
  },
  matchdayMeta: {
    color: theme.colors.text.muted,
    fontSize: theme.typography.sizes.xs,
    lineHeight: 16,
  },
  matchdayRail: {
    marginHorizontal: -theme.spacing.xs,
  },
  matchdayRailContent: {
    gap: theme.spacing.xs,
    paddingHorizontal: theme.spacing.xs,
  },
  matchdayChip: {
    alignItems: 'center',
    backgroundColor: theme.colors.football.surface,
    borderColor: theme.colors.football.border,
    borderRadius: 14,
    borderWidth: 1,
    minWidth: 46,
    paddingHorizontal: theme.spacing.sm,
    paddingVertical: theme.spacing.xs,
  },
  matchdayChipActive: {
    backgroundColor: theme.colors.football.neon,
    borderColor: theme.colors.football.neon,
  },
  matchdayChipText: {
    color: theme.colors.text.secondary,
    fontSize: theme.typography.sizes.xs,
    fontWeight: theme.typography.weights.bold,
    lineHeight: 16,
  },
  matchdayChipTextActive: {
    color: theme.colors.football.blackPure,
  },
  sectionHeader: {
    gap: theme.spacing.xs,
    paddingHorizontal: theme.spacing.xs,
  },
  tableHeader: {
    alignItems: 'center',
    backgroundColor: theme.colors.football.neon,
    flexDirection: 'row',
    paddingHorizontal: theme.spacing.md,
    paddingVertical: theme.spacing.md,
  },
  tableHeaderText: {
    color: theme.colors.football.blackPure,
    fontSize: theme.typography.sizes.xs,
    fontWeight: theme.typography.weights.bold,
    textAlign: 'center',
    textTransform: 'uppercase',
  },
  positionColumn: {
    width: 32,
  },
  teamColumn: {
    flex: 1,
    textAlign: 'left',
  },
  statColumn: {
    width: 44,
  },
  sectionTitle: {
    color: theme.colors.text.primary,
    fontSize: theme.typography.sizes.xl,
    fontWeight: theme.typography.weights.bold,
  },
  sectionSubtitle: {
    color: theme.colors.text.muted,
    fontSize: theme.typography.sizes.sm,
  },
});
