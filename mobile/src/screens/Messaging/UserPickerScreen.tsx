import { useCallback, useEffect, useState } from 'react';
import { ActivityIndicator, FlatList, StyleSheet, View } from 'react-native';
import { NativeStackScreenProps } from '@react-navigation/native-stack';

import { UserListItem } from '../../components/messaging/UserListItem';
import { AppButton } from '../../components/ui/AppButton';
import { AppInput } from '../../components/ui/AppInput';
import { AppText } from '../../components/ui/AppText';
import { ErrorMessage } from '../../components/ui/ErrorMessage';
import { Screen } from '../../components/ui/Screen';
import { theme } from '../../config/theme';
import { MessagingStackParamList } from '../../navigation/navigation.types';
import { toApiError } from '../../services/api/apiError';
import { messagingService } from '../../services/messaging/messagingService';
import { userDirectoryService } from '../../services/user/userDirectoryService';
import { MessagingUser } from '../../types/userDirectory.types';

type UserPickerScreenProps = NativeStackScreenProps<MessagingStackParamList, 'UserPicker'>;

export function UserPickerScreen({ navigation }: UserPickerScreenProps) {
  const [users, setUsers] = useState<MessagingUser[]>([]);
  const [query, setQuery] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [selectedUserId, setSelectedUserId] = useState<number | null>(null);

  const loadUsers = useCallback(async () => {
    setError(null);
    setIsLoading(true);

    try {
      setUsers(await userDirectoryService.listUsers(query.trim()));
    } catch (caughtError) {
      setError(toApiError(caughtError).message);
    } finally {
      setIsLoading(false);
    }
  }, [query]);

  useEffect(() => {
    const timeout = setTimeout(() => {
      void loadUsers();
    }, 250);

    return () => clearTimeout(timeout);
  }, [loadUsers]);

  async function openConversation(user: MessagingUser) {
    setSelectedUserId(user.id);
    setError(null);

    try {
      const conversation = await messagingService.createOrOpenConversation(user.id);
      navigation.replace('ConversationDetail', {
        conversationId: conversation.id,
        participantUsername: conversation.participant?.username ?? user.username,
      });
    } catch (caughtError) {
      setError(toApiError(caughtError).message);
    } finally {
      setSelectedUserId(null);
    }
  }

  return (
    <Screen style={styles.screen}>
      <FlatList
        contentContainerStyle={styles.content}
        data={users}
        keyExtractor={(user) => String(user.id)}
        keyboardShouldPersistTaps="handled"
        ListEmptyComponent={!isLoading ? <EmptyUsers /> : null}
        ListHeaderComponent={
          <View style={styles.header}>
            <View style={styles.titleBlock}>
              <AppText style={styles.kicker}>Nouvelle conversation</AppText>
              <AppText variant="title">Choisir un utilisateur</AppText>
              <AppText variant="subtitle">L'annuaire expose uniquement les champs publics nécessaires.</AppText>
            </View>

            <AppButton label="Retour messages" onPress={() => navigation.goBack()} variant="secondary" />

            <AppInput
              label="Recherche"
              onChangeText={setQuery}
              placeholder="Username ou nom affiché"
              value={query}
            />

            <ErrorMessage message={error} />
            {isLoading ? <ActivityIndicator color={theme.colors.accent} /> : null}
          </View>
        }
        renderItem={({ item }) => (
          <UserListItem
            user={item}
            onPress={() => {
              if (selectedUserId === null) {
                void openConversation(item);
              }
            }}
          />
        )}
      />
    </Screen>
  );
}

function EmptyUsers() {
  return (
    <View style={styles.empty}>
      <AppText style={styles.emptyTitle}>Aucun utilisateur trouvé</AppText>
      <AppText variant="muted">Essaie avec un autre nom utilisateur.</AppText>
    </View>
  );
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
