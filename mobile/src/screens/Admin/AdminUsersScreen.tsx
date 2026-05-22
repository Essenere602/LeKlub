import { useCallback, useEffect, useState } from 'react';
import { Alert, FlatList, RefreshControl, StyleSheet, View } from 'react-native';
import { NativeStackScreenProps } from '@react-navigation/native-stack';

import { AdminSuspendUserModal } from '../../components/admin/AdminSuspendUserModal';
import { AdminUserCard } from '../../components/admin/AdminUserCard';
import { AppButton } from '../../components/ui/AppButton';
import { AppHeader } from '../../components/ui/AppHeader';
import { AppInput } from '../../components/ui/AppInput';
import { EmptyState } from '../../components/ui/EmptyState';
import { ErrorState } from '../../components/ui/ErrorState';
import { LoadingState } from '../../components/ui/LoadingState';
import { Screen } from '../../components/ui/Screen';
import { theme } from '../../config/theme';
import { AdminStackParamList } from '../../navigation/navigation.types';
import { useAuth } from '../../hooks/useAuth';
import { toApiError } from '../../services/api/apiError';
import { adminService } from '../../services/admin/adminService';
import { AdminUser } from '../../types/admin.types';
import { Pagination } from '../../types/feed.types';

type AdminUsersScreenProps = NativeStackScreenProps<AdminStackParamList, 'AdminUsers'>;

const LIMIT = 10;

export function AdminUsersScreen({ navigation }: AdminUsersScreenProps) {
  const { user: currentUser } = useAuth();
  const [users, setUsers] = useState<AdminUser[]>([]);
  const [pagination, setPagination] = useState<Pagination | null>(null);
  const [query, setQuery] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [isRefreshing, setIsRefreshing] = useState(false);
  const [isLoadingMore, setIsLoadingMore] = useState(false);
  const [selectedUser, setSelectedUser] = useState<AdminUser | null>(null);
  const [actionUserId, setActionUserId] = useState<number | null>(null);

  const loadUsers = useCallback(async (page = 1, append = false) => {
    const result = await adminService.listUsers(page, LIMIT, query.trim());
    setPagination(result.pagination);
    setUsers((currentUsers) => (append ? [...currentUsers, ...result.users] : result.users));
  }, [query]);

  const loadInitialUsers = useCallback(async () => {
    setError(null);
    setIsLoading(true);

    try {
      await loadUsers();
    } catch (caughtError) {
      setError(toApiError(caughtError).message);
    } finally {
      setIsLoading(false);
    }
  }, [loadUsers]);

  useEffect(() => {
    const timeout = setTimeout(() => {
      void loadInitialUsers();
    }, 250);

    return () => clearTimeout(timeout);
  }, [loadInitialUsers]);

  async function refreshUsers() {
    setError(null);
    setIsRefreshing(true);

    try {
      await loadUsers();
    } catch (caughtError) {
      setError(toApiError(caughtError).message);
    } finally {
      setIsRefreshing(false);
    }
  }

  async function loadMoreUsers() {
    if (!pagination || pagination.page >= pagination.pages || isLoadingMore) {
      return;
    }

    setError(null);
    setIsLoadingMore(true);

    try {
      await loadUsers(pagination.page + 1, true);
    } catch (caughtError) {
      setError(toApiError(caughtError).message);
    } finally {
      setIsLoadingMore(false);
    }
  }

  async function suspendSelectedUser(payload: { durationDays: 1 | 7 | 30; reason: string }) {
    if (!selectedUser) {
      return;
    }

    setActionUserId(selectedUser.id);
    setError(null);

    try {
      await adminService.suspendUser(selectedUser.id, payload);
      setSelectedUser(null);
      await loadUsers();
    } catch (caughtError) {
      setError(toApiError(caughtError).message);
      throw caughtError;
    } finally {
      setActionUserId(null);
    }
  }

  function confirmUnsuspend(user: AdminUser) {
    Alert.alert(
      'Lever la suspension',
      `Le compte @${user.username} pourra de nouveau effectuer des actions d'écriture.`,
      [
        { text: 'Annuler', style: 'cancel' },
        { text: 'Confirmer', onPress: () => { void unsuspendUser(user); } },
      ],
    );
  }

  async function unsuspendUser(user: AdminUser) {
    setActionUserId(user.id);
    setError(null);

    try {
      await adminService.unsuspendUser(user.id, { reason: 'Suspension levée par la modération.' });
      await loadUsers();
    } catch (caughtError) {
      setError(toApiError(caughtError).message);
    } finally {
      setActionUserId(null);
    }
  }

  function confirmPromoteAdmin(user: AdminUser) {
    Alert.alert(
      'Promouvoir administrateur',
      `Donner le rôle administrateur à @${user.username} ? Ce compte aura accès aux fonctions d'administration.`,
      [
        { text: 'Annuler', style: 'cancel' },
        { text: 'Confirmer', onPress: () => { void promoteUserToAdmin(user); } },
      ],
    );
  }

  async function promoteUserToAdmin(user: AdminUser) {
    setActionUserId(user.id);
    setError(null);

    try {
      await adminService.promoteUserToAdmin(user.id);
      await loadUsers();
    } catch (caughtError) {
      setError(toApiError(caughtError).message);
    } finally {
      setActionUserId(null);
    }
  }

  function confirmDemoteAdmin(user: AdminUser) {
    Alert.alert(
      'Retirer le rôle admin',
      `Retirer les accès administrateur de @${user.username} ?`,
      [
        { text: 'Annuler', style: 'cancel' },
        { text: 'Confirmer', style: 'destructive', onPress: () => { void demoteAdminUser(user); } },
      ],
    );
  }

  async function demoteAdminUser(user: AdminUser) {
    setActionUserId(user.id);
    setError(null);

    try {
      await adminService.demoteAdminUser(user.id);
      await loadUsers();
    } catch (caughtError) {
      setError(toApiError(caughtError).message);
    } finally {
      setActionUserId(null);
    }
  }

  return (
    <Screen style={styles.screen}>
      <FlatList
        contentContainerStyle={styles.content}
        data={users}
        keyExtractor={(user) => String(user.id)}
        ListEmptyComponent={!isLoading && !error ? (
          <EmptyState message="Aucun utilisateur ne correspond à cette recherche." title="Aucun utilisateur" />
        ) : null}
        ListFooterComponent={pagination && pagination.page < pagination.pages ? (
          <AppButton label="Charger plus" loading={isLoadingMore} onPress={loadMoreUsers} variant="secondary" />
        ) : null}
        ListHeaderComponent={
          <View style={styles.header}>
            <AppButton label="Retour admin" onPress={() => navigation.goBack()} variant="secondary" />
            <AppHeader
              kicker="Administration"
              subtitle="Liste sécurisée sans email, mot de passe ou token."
              title="Utilisateurs"
            />
            <AppInput label="Recherche" onChangeText={setQuery} placeholder="Username ou nom affiché" value={query} />
            {isLoading ? <LoadingState message="Chargement des utilisateurs..." /> : null}
            {error ? <ErrorState message={error} onRetry={loadInitialUsers} /> : null}
          </View>
        }
        refreshControl={
          <RefreshControl refreshing={isRefreshing} tintColor={theme.colors.accent} onRefresh={refreshUsers} />
        }
        renderItem={({ item }) => (
          <AdminUserCard
            currentUserId={currentUser?.id}
            loading={actionUserId === item.id}
            user={item}
            onDemoteAdmin={() => confirmDemoteAdmin(item)}
            onPromoteAdmin={() => confirmPromoteAdmin(item)}
            onSuspend={() => setSelectedUser(item)}
            onUnsuspend={() => confirmUnsuspend(item)}
          />
        )}
      />
      <AdminSuspendUserModal
        error={error}
        loading={selectedUser !== null && actionUserId === selectedUser.id}
        user={selectedUser}
        visible={selectedUser !== null}
        onClose={() => setSelectedUser(null)}
        onSubmit={suspendSelectedUser}
      />
    </Screen>
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
});
