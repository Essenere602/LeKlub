import { useCallback, useEffect, useRef, useState } from 'react';
import { ActivityIndicator, FlatList, KeyboardAvoidingView, Platform, StyleSheet, View } from 'react-native';
import { NativeStackScreenProps } from '@react-navigation/native-stack';

import { MessageBubble } from '../../components/messaging/MessageBubble';
import { AppButton } from '../../components/ui/AppButton';
import { AppInput } from '../../components/ui/AppInput';
import { AppText } from '../../components/ui/AppText';
import { ErrorMessage } from '../../components/ui/ErrorMessage';
import { Screen } from '../../components/ui/Screen';
import { theme } from '../../config/theme';
import { useAuth } from '../../hooks/useAuth';
import { useMessagingSocket } from '../../hooks/useMessagingSocket';
import { MainStackParamList } from '../../navigation/navigation.types';
import { toApiError } from '../../services/api/apiError';
import { messagingService } from '../../services/messaging/messagingService';
import { PrivateMessage } from '../../types/messaging.types';

type ConversationDetailScreenProps = NativeStackScreenProps<MainStackParamList, 'ConversationDetail'>;

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

  return (
    <Screen style={styles.screen}>
      <KeyboardAvoidingView
        behavior={Platform.OS === 'ios' ? 'padding' : undefined}
        style={styles.container}
      >
        <View style={styles.header}>
          <AppButton label="Retour messages" onPress={() => navigation.goBack()} variant="secondary" />
          <View style={styles.titleBlock}>
            <AppText style={styles.kicker}>Conversation privée</AppText>
            <AppText variant="title">{participantUsername}</AppText>
            <AppText style={styles.socketStatus}>WebSocket : {labelForSocketStatus(socketStatus)}</AppText>
          </View>
          <ErrorMessage message={error} />
        </View>

        {isLoading ? (
          <ActivityIndicator color={theme.colors.accent} />
        ) : (
          <FlatList
            ref={listRef}
            contentContainerStyle={styles.messages}
            data={messages}
            keyExtractor={(message) => String(message.id)}
            ListEmptyComponent={<EmptyMessages />}
            renderItem={({ item }) => (
              <MessageBubble message={item} mine={item.sender.id === user?.id} />
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

function EmptyMessages() {
  return (
    <View style={styles.empty}>
      <AppText style={styles.emptyTitle}>Aucun message</AppText>
      <AppText variant="muted">Envoie le premier Message privé de cette conversation.</AppText>
    </View>
  );
}

function labelForSocketStatus(status: string): string {
  const labels: Record<string, string> = {
    authenticated: 'connecté',
    closed: 'déconnecté',
    connected: 'connexion ouverte',
    connecting: 'connexion...',
    disabled: 'désactivé',
    error: 'erreur',
  };

  return labels[status] ?? status;
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
  kicker: {
    color: theme.colors.accent,
    fontSize: theme.typography.sizes.sm,
    fontWeight: theme.typography.weights.bold,
    textTransform: 'uppercase',
  },
  socketStatus: {
    color: theme.colors.text.muted,
    fontSize: theme.typography.sizes.sm,
  },
  messages: {
    flexGrow: 1,
    gap: theme.spacing.md,
    justifyContent: 'flex-end',
    paddingVertical: theme.spacing.md,
  },
  composer: {
    backgroundColor: theme.colors.background,
    gap: theme.spacing.md,
  },
  input: {
    minHeight: 74,
    paddingVertical: theme.spacing.md,
    textAlignVertical: 'top',
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
