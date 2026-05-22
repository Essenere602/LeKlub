import { useCallback, useEffect, useRef, useState } from 'react';
import { FlatList, KeyboardAvoidingView, Platform, StyleSheet, View } from 'react-native';
import { NativeStackScreenProps } from '@react-navigation/native-stack';

import { MessageBubble } from '../../components/messaging/MessageBubble';
import { AppButton } from '../../components/ui/AppButton';
import { AppHeader } from '../../components/ui/AppHeader';
import { AppInput } from '../../components/ui/AppInput';
import { EmptyState } from '../../components/ui/EmptyState';
import { ErrorState } from '../../components/ui/ErrorState';
import { LoadingState } from '../../components/ui/LoadingState';
import { Screen } from '../../components/ui/Screen';
import { StatusBadge } from '../../components/ui/StatusBadge';
import { theme } from '../../config/theme';
import { useAuth } from '../../hooks/useAuth';
import { useMessagingSocket } from '../../hooks/useMessagingSocket';
import { MessagingStackParamList } from '../../navigation/navigation.types';
import { toApiError } from '../../services/api/apiError';
import { messagingService } from '../../services/messaging/messagingService';
import { PrivateMessage } from '../../types/messaging.types';

type ConversationDetailScreenProps = NativeStackScreenProps<MessagingStackParamList, 'ConversationDetail'>;

export function ConversationDetailScreen({ navigation, route }: ConversationDetailScreenProps) {
  const { conversationId, participantUsername } = route.params;
  const { isAuthenticated, user } = useAuth();
  const listRef = useRef<FlatList<PrivateMessage>>(null);
  const [messages, setMessages] = useState<PrivateMessage[]>([]);
  const [content, setContent] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [isSending, setIsSending] = useState(false);

  const loadMessages = useCallback(async () => {
    const result = await messagingService.listMessages(conversationId);
    setMessages(result);
    await messagingService.markAsRead(conversationId);
  }, [conversationId]);

  const loadInitialMessages = useCallback(async () => {
    setError(null);
    setIsLoading(true);

    try {
      await loadMessages();
    } catch (caughtError) {
      setError(toApiError(caughtError).message);
    } finally {
      setIsLoading(false);
    }
  }, [loadMessages]);

  const socketStatus = useMessagingSocket({
    enabled: isAuthenticated,
    onNewMessage: useCallback((event) => {
      if (event.conversationId === conversationId) {
        void loadMessages();
      }
    }, [conversationId, loadMessages]),
  });

  useEffect(() => {
    void loadInitialMessages();
  }, [loadInitialMessages]);

  useEffect(() => {
    if (messages.length > 0) {
      requestAnimationFrame(() => listRef.current?.scrollToEnd({ animated: true }));
    }
  }, [messages.length]);

  async function sendMessage() {
    const trimmedContent = content.trim();
    setError(null);

    if (!trimmedContent) {
      setError('Le message est requis.');
      return;
    }

    setIsSending(true);

    try {
      await messagingService.sendMessage(conversationId, trimmedContent);
      setContent('');
      await loadMessages();
    } catch (caughtError) {
      setError(toApiError(caughtError).message);
    } finally {
      setIsSending(false);
    }
  }

  async function hideMessageForMe(messageId: number) {
    setError(null);

    try {
      await messagingService.hideMessageForMe(conversationId, messageId);
      await loadMessages();
    } catch (caughtError) {
      setError(toApiError(caughtError).message);
      throw caughtError;
    }
  }

  return (
    <Screen style={styles.screen}>
      <KeyboardAvoidingView
        behavior={Platform.OS === 'ios' ? 'padding' : undefined}
        keyboardVerticalOffset={Platform.OS === 'ios' ? 12 : 0}
        style={styles.container}
      >
        <View style={styles.header}>
          <AppButton label="Retour messages" onPress={() => navigation.goBack()} variant="secondary" />
          <View style={styles.titleBlock}>
            <AppHeader kicker="Conversation privée" title={participantUsername} />
            <StatusBadge {...badgeForSocketStatus(socketStatus)} />
          </View>
          {error ? <ErrorState message={error} onRetry={loadInitialMessages} /> : null}
        </View>

        {isLoading ? (
          <LoadingState message="Chargement des messages..." />
        ) : (
          <FlatList
            ref={listRef}
            contentContainerStyle={styles.messages}
            data={messages}
            keyExtractor={(message) => String(message.id)}
            keyboardShouldPersistTaps="handled"
            ListEmptyComponent={
              <EmptyState
                icon="chatbubble-outline"
                message="Envoie le premier Message privé de cette conversation."
                title="Aucun message"
              />
            }
            renderItem={({ item }) => (
              <MessageBubble
                message={item}
                mine={item.sender.id === user?.id}
                onHideForMe={() => hideMessageForMe(item.id)}
              />
            )}
          />
        )}

        <View style={styles.composer}>
          <AppInput
            label="Message"
            multiline
            onChangeText={setContent}
            placeholder="Écrire un message..."
            style={styles.input}
            value={content}
          />
          <AppButton label="Envoyer" loading={isSending} onPress={sendMessage} />
        </View>
      </KeyboardAvoidingView>
    </Screen>
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
  container: {
    flex: 1,
    gap: theme.spacing.md,
    padding: theme.spacing.xl,
  },
  header: {
    gap: theme.spacing.md,
  },
  titleBlock: {
    gap: theme.spacing.xs,
  },
  messages: {
    flexGrow: 1,
    gap: theme.spacing.md,
    justifyContent: 'flex-end',
    paddingVertical: theme.spacing.md,
  },
  composer: {
    backgroundColor: theme.colors.background,
    borderTopColor: theme.colors.border,
    borderTopWidth: 1,
    gap: theme.spacing.md,
    paddingTop: theme.spacing.md,
  },
  input: {
    minHeight: 74,
    paddingVertical: theme.spacing.md,
    textAlignVertical: 'top',
  },
});
