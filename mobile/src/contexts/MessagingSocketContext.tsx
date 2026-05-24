import { createContext, PropsWithChildren, useCallback, useEffect, useMemo, useRef, useState } from 'react';

import { useAuth } from '../hooks/useAuth';
import { connectMessagingSocket, MessagingSocketStatus } from '../services/messaging/messagingSocket';
import { tokenStorage } from '../services/auth/tokenStorage';
import { NewMessageSocketEvent } from '../types/messaging.types';

type MessagingSocketListener = (event: NewMessageSocketEvent) => void;

type MessagingSocketContextValue = {
  status: MessagingSocketStatus;
  addNewMessageListener: (listener: MessagingSocketListener) => () => void;
};

export const MessagingSocketContext = createContext<MessagingSocketContextValue | null>(null);

export function MessagingSocketProvider({ children }: PropsWithChildren) {
  const { isAuthenticated } = useAuth();
  const listeners = useRef(new Set<MessagingSocketListener>());
  const [status, setStatus] = useState<MessagingSocketStatus>(isAuthenticated ? 'connecting' : 'disabled');

  const addNewMessageListener = useCallback((listener: MessagingSocketListener) => {
    listeners.current.add(listener);

    return () => {
      listeners.current.delete(listener);
    };
  }, []);

  useEffect(() => {
    let isActive = true;
    let connection: { close: () => void } | null = null;

    async function connect() {
      if (!isAuthenticated) {
        setStatus('disabled');
        return;
      }

      setStatus('connecting');
      const token = await tokenStorage.getAccessToken();

      if (!isActive) {
        return;
      }

      if (!token) {
        setStatus('disabled');
        return;
      }

      connection = connectMessagingSocket(token, {
        onNewMessage: (event) => {
          listeners.current.forEach((listener) => listener(event));
        },
        onStatusChange: (nextStatus) => {
          if (isActive) {
            setStatus(nextStatus);
          }
        },
      });
    }

    void connect();

    return () => {
      isActive = false;
      connection?.close();
    };
  }, [isAuthenticated]);

  const value = useMemo<MessagingSocketContextValue>(() => ({
    addNewMessageListener,
    status,
  }), [addNewMessageListener, status]);

  return (
    <MessagingSocketContext.Provider value={value}>
      {children}
    </MessagingSocketContext.Provider>
  );
}
