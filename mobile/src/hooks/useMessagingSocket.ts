import { useEffect, useState } from 'react';

import { connectMessagingSocket, MessagingSocketStatus } from '../services/messaging/messagingSocket';
import { tokenStorage } from '../services/auth/tokenStorage';
import { NewMessageSocketEvent } from '../types/messaging.types';

type UseMessagingSocketOptions = {
  enabled: boolean;
  onNewMessage: (event: NewMessageSocketEvent) => void;
};

export function useMessagingSocket({ enabled, onNewMessage }: UseMessagingSocketOptions): MessagingSocketStatus {
  const [status, setStatus] = useState<MessagingSocketStatus>(enabled ? 'connecting' : 'disabled');

  useEffect(() => {
    let isActive = true;
    let connection: { close: () => void } | null = null;

    async function connect() {
      if (!enabled) {
        setStatus('disabled');
        return;
      }

      const token = await tokenStorage.getAccessToken();
      if (!isActive) {
        return;
      }

      if (!token) {
        setStatus('disabled');
        return;
      }

      connection = connectMessagingSocket(token, {
        onNewMessage,
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
  }, [enabled, onNewMessage]);

  return status;
}
