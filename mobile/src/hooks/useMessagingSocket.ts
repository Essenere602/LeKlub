import { useContext, useEffect } from 'react';

import { MessagingSocketContext } from '../contexts/MessagingSocketContext';
import { MessagingSocketStatus } from '../services/messaging/messagingSocket';
import { NewMessageSocketEvent } from '../types/messaging.types';

type UseMessagingSocketOptions = {
  enabled: boolean;
  onNewMessage: (event: NewMessageSocketEvent) => void;
};

export function useMessagingSocket({ enabled, onNewMessage }: UseMessagingSocketOptions): MessagingSocketStatus {
  const context = useContext(MessagingSocketContext);

  if (!context) {
    throw new Error('useMessagingSocket must be used within MessagingSocketProvider.');
  }

  useEffect(() => {
    if (!enabled) {
      return undefined;
    }

    return context.addNewMessageListener(onNewMessage);
  }, [context, enabled, onNewMessage]);

  return enabled ? context.status : 'disabled';
}
