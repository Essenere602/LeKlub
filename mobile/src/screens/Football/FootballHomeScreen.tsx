import { useCallback, useEffect, useState } from 'react';
import { FlatList, StyleSheet, View } from 'react-native';
import { NativeStackScreenProps } from '@react-navigation/native-stack';

import { CompetitionCard } from '../../components/football/CompetitionCard';
import { FootballEmptyState } from '../../components/football/FootballEmptyState';
import { FootballShell } from '../../components/football/FootballShell';
import { Screen } from '../../components/ui/Screen';
import { theme } from '../../config/theme';
import { MainStackParamList } from '../../navigation/navigation.types';
import { toApiError } from '../../services/api/apiError';
import { footballService } from '../../services/football/footballService';
import { FootballCompetition } from '../../types/football.types';

type FootballHomeScreenProps = NativeStackScreenProps<MainStackParamList, 'Football'>;

export function FootballHomeScreen({ navigation }: FootballHomeScreenProps) {
  const [competitions, setCompetitions] = useState<FootballCompetition[]>([]);
  const [error, setError] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  const loadCompetitions = useCallback(async () => {
    setError(null);
    setIsLoading(true);

    try {
      setCompetitions(await footballService.listCompetitions());
    } catch (caughtError) {
      setError(toApiError(caughtError).message);
    } finally {
      setIsLoading(false);
    }
  }, []);

  useEffect(() => {
    void loadCompetitions();
  }, [loadCompetitions]);

  return (
    <Screen style={styles.screen}>
      <FootballShell
        icon="trophy"
        subtitle="Résultats, prochains matchs, classements et buteurs des championnats suivis par LeKlub."
        title="Compétitions"
      >
        <FlatList
          contentContainerStyle={styles.content}
          data={competitions}
          keyExtractor={(competition) => competition.code}
          ListEmptyComponent={!isLoading && !error ? <EmptyState /> : null}
          ListHeaderComponent={
            <View style={styles.header}>
              {isLoading ? (
                <FootballEmptyState
                  message="Chargement des championnats supportés par LeKlub."
                  title="Chargement"
                  type="loading"
                />
              ) : null}

              {error ? (
                <FootballEmptyState
                  icon="warning-outline"
                  message={error}
                  onRetry={loadCompetitions}
                  title="Impossible de charger les compétitions"
                  type="error"
                />
              ) : null}
            </View>
          }
          renderItem={({ item }) => (
            <CompetitionCard
              competition={item}
              onPress={() => navigation.navigate('FootballCompetition', {
                code: item.code,
                country: item.country,
                name: item.name,
              })}
            />
          )}
        />
      </FootballShell>
    </Screen>
  );
}

function EmptyState() {
  return (
    <FootballEmptyState
      message="Réessayez dans un instant."
      title="Aucune compétition disponible"
    />
  );
}

const styles = StyleSheet.create({
  screen: {
    padding: 0,
  },
  content: {
    gap: theme.spacing.md,
    padding: theme.spacing.lg,
    paddingTop: theme.spacing.xl,
  },
  header: {
    gap: theme.spacing.lg,
  },
});
