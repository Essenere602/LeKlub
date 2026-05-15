import { AxiosError } from 'axios';
import { createContext, PropsWithChildren, useCallback, useEffect, useMemo, useState } from 'react';

import { LoginCredentials, RegisterPayload } from '../types/auth.types';
import { User } from '../types/user.types';
import { authService } from '../services/auth/authService';
import { tokenStorage } from '../services/auth/tokenStorage';

type AuthContextValue = {
  user: User | null;
  isAuthenticated: boolean;
  isLoading: boolean;
  login: (credentials: LoginCredentials) => Promise<void>;
  register: (payload: RegisterPayload) => Promise<void>;
  logout: () => Promise<void>;
  refreshCurrentUser: () => Promise<void>;
};

export const AuthContext = createContext<AuthContextValue | null>(null);

export function AuthProvider({ children }: PropsWithChildren) {
  const [user, setUser] = useState<User | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  const clearSession = useCallback(async () => {
    await tokenStorage.clearAccessToken();
    setUser(null);
  }, []);

  const refreshCurrentUser = useCallback(async () => {
    try {
      const currentUser = await authService.getCurrentUser();
      setUser(currentUser);
    } catch (error) {
      if (error instanceof AxiosError && error.response?.status === 401) {
        await clearSession();
        return;
      }

      throw error;
    }
  }, [clearSession]);

  useEffect(() => {
    async function bootstrapSession() {
      const token = await tokenStorage.getAccessToken();

      if (!token) {
        setIsLoading(false);
        return;
      }

      try {
        await refreshCurrentUser();
      } catch {
        await clearSession();
      } finally {
        setIsLoading(false);
      }
    }

    void bootstrapSession();
  }, [clearSession, refreshCurrentUser]);

  const login = useCallback(async (credentials: LoginCredentials) => {
    await authService.login(credentials);
    const currentUser = await authService.getCurrentUser();
    setUser(currentUser);
  }, []);

  const register = useCallback(async (payload: RegisterPayload) => {
    await authService.register(payload);
  }, []);

  const logout = useCallback(async () => {
    await authService.logout();
    setUser(null);
  }, []);

  const value = useMemo<AuthContextValue>(() => ({
    user,
    isAuthenticated: user !== null,
    isLoading,
    login,
    register,
    logout,
    refreshCurrentUser,
  }), [isLoading, login, logout, refreshCurrentUser, register, user]);

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}
