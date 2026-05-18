import { useCallback, useEffect, useState } from 'react';
import { ActivityIndicator, FlatList, RefreshControl, StyleSheet, View } from 'react-native';
import { NativeStackScreenProps } from '@react-navigation/native-stack';

import { ConversationCard } from '../../components/messaging/ConversationCard';
import { AppButton } from '../../components/ui/AppButton';
import { AppText } from '../../components/ui/AppText';
import { ErrorMessage } from '../../components/ui/ErrorMessage';
import { Screen } from '../../components/ui/Screen';
import { StatusBadge } from '../../components/ui/StatusBadge';
import { theme } from '../../config/theme';
import { useAuth } from '../../hooks/useAuth';
import { useMessagingSocket } from '../../hooks/useMessagingSocket';
import { MessagingStackParamList } from '../../navigation/navigation.types';
import { toApiError } from '../../services/api/apiError';
import { messagingService } from '../../services/messaging/messagingService';
import { Conversation } from '../../types/messaging.types';

type ConversationListScreenProps = NativeStackScreenProps<MessagingStackParamList, 'Conversations'>;

export function ConversationListScreen({ navigation }: ConversationListScreenProps) {
  const { isAuthenticated } = useAuth();
  const [conversations, setConversations] = useState<Conversation[]>([]);
  const [error, setError] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [isRefreshing, setIsRefreshing] = useState(false);

  const loadConversations = useCallback(async () => {
    const result = await messagingService.listConversations();
    setConversations(result);
  }, []);

  const loadInitialConversations = useCallback(async () => {
    setError(null);
    setIsLoading(true);

    try {
      await loadConversations();
    } catch (caughtError) {
      setError(toApiError(caughtError).message);
    } finally {
      setIsLoading(false);
    }
  }, [loadConversations]);

  const socketStatus = useMessagingSocket({
    enabled: isAuthenticated,
    onNewMessage: useCallback(() => {
      void loadConversations();
    }, [loadConversations]),
  });

  useEffect(() => {
    void loadInitialConversations();
  }, [loadInitialConversations]);

  async function refreshConversations() {
    setError(null);
    setIsRefreshing(true);

    try {
      await loadConversations();
    } catch (caughtError) {
      setError(toApiError(caughtError).message);
    } finally {
      setIsRefreshing(false);
    }
  }

  return (
    <Screen style={styles.screen}>
      <FlatList
        contentContainerStyle={styles.content}
        data={conversations}
        keyExtractor={(conversation) => String(conversation.id)}
        ListEmptyComponent={!isLoading ? <EmptyConversations /> : null}
        ListHeaderComponent={
          <View style={styles.header}>
            <View style={styles.titleBlock}>
              <AppText style={styles.kicker}>Messages</AppText>
              <AppText variant="title">Conversations privées</AppText>
              <AppText variant="subtitle">Le temps réel signale les nouveaux messages, puis l'app recharge via l'API.</AppText>
            </View>

            <View style={styles.actions}>
              <AppButton label="Retour" onPress={() => navigation.goBack()} variant="secondary" />
              <AppButton label="Nouvelle conversation" onPress={() => navigation.navigate('UserPicker')} />
            </View>

            <StatusBadge {...badgeForSocketStatus(socketStatus)} />
            <ErrorMessage message={error} />
            {isLoading ? <ActivityIndicator color={theme.colors.accent} /> : null}
          </View>
        }
        refreshControl={
          <RefreshControl
            refreshing={isRefreshing}
            tintColor={theme.colors.accent}
            onRefresh={refreshConversations}
          />
        }
        renderItem={({ item }) => (
          <ConversationCard
            conversation={item}
            onPress={() => navigation.navigate('ConversationDetail', {
              conversationId: item.id,
              participantUsername: item.participant?.username ?? 'Conversation',
            })}
          />
        )}
      />
    </Screen>
  );
}

function EmptyConversations() {
  return (
    <View style={styles.empty}>
      <AppText style={styles.emptyTitle}>Aucune conversation</AppText>
      <AppText variant="muted">Choisis un utilisateur pour démarrer une Conversation privée.</AppText>
    </View>
  );
}

type SocketBadge = {
  label: string;
  variant: 'accent' | 'success' | 'warning' | 'danger' | 'neutral';
};

function badgeForSocketStatus(status: string): SocketBadge {
  if (status === 'authenticated' || status === 'connected') {
    return { label: 'Temps réel actif', variant: 'success' };
  }

  if (status === 'connecting') {
    return { label: 'Connexion temps réel...', variant: 'warning' };
  }

  if (status === 'error') {
    return { label: 'Temps réel indisponible', variant: 'danger' };
  }

  return { label: 'Hors ligne', variant: 'neutral' };
}

const styles = StyleSheet.create({
  screen: {
    padding: 0,
  },
  content: {
    gap: theme.spacing.md,
    padding: theme.spacing.xl,
  },
  header: {
    gap: theme.spacing.lg,
  },
  titleBlock: {
    gap: theme.spacing.sm,
  },
  kicker: {
    color: theme.colors.accent,
    fontSize: theme.typography.sizes.sm,
    fontWeight: theme.typography.weights.bold,
    textTransform: 'uppercase',
  },
  actions: {
    gap: theme.spacing.md,
  },
  empty: {
    alignItems: 'center',
    backgroundColor: theme.colors.surface,
    borderColor: theme.colors.border,
    borderRadius: theme.radius.lg,
    borderWidth: 1,
    gap: theme.spacing.sm,
    padding: theme.spacing.xl,
  },
  emptyTitle: {
    fontSize: theme.typography.sizes.lg,
    fontWeight: theme.typography.weights.bold,
  },
});
