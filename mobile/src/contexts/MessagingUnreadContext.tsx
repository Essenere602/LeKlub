import { createContext, PropsWithChildren, useCallback, useEffect, useMemo, useState } from 'react';

import { useAuth } from '../hooks/useAuth';
import { useMessagingSocket } from '../hooks/useMessagingSocket';
import { messagingService } from '../services/messaging/messagingService';
import { Conversation } from '../types/messaging.types';

type MessagingUnreadContextValue = {
  unreadCount: number;
  refreshUnreadCount: () => Promise<void>;
  setUnreadCountFromConversations: (conversations: Conversation[]) => void;
};

export const MessagingUnreadContext = createContext<MessagingUnreadContextValue | null>(null);

export function MessagingUnreadProvider({ children }: PropsWithChildren) {
  const { isAuthenticated } = useAuth();
  const [unreadCount, setUnreadCount] = useState(0);

  const setUnreadCountFromConversations = useCallback((conversations: Conversation[]) => {
    setUnreadCount(totalUnreadFromConversations(conversations));
  }, []);

  const refreshUnreadCount = useCallback(async () => {
    if (!isAuthenticated) {
      setUnreadCount(0);
      return;
    }

    const conversations = await messagingService.listConversations();
    setUnreadCountFromConversations(conversations);
  }, [isAuthenticated, setUnreadCountFromConversations]);

  useMessagingSocket({
    enabled: isAuthenticated,
    onNewMessage: useCallback(() => {
      void refreshUnreadCount();
    }, [refreshUnreadCount]),
  });

  useEffect(() => {
    if (!isAuthenticated) {
      setUnreadCount(0);
      return;
    }

    void refreshUnreadCount();
  }, [isAuthenticated, refreshUnreadCount]);

  const value = useMemo<MessagingUnreadContextValue>(() => ({
    refreshUnreadCount,
    setUnreadCountFromConversations,
    unreadCount,
  }), [refreshUnreadCount, setUnreadCountFromConversations, unreadCount]);

  return (
    <MessagingUnreadContext.Provider value={value}>
      {children}
    </MessagingUnreadContext.Provider>
  );
}

function totalUnreadFromConversations(conversations: Conversation[]): number {
  return conversations.reduce((total, conversation) => total + conversation.unreadCount, 0);
}
