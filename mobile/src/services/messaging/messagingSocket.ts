import { config } from '../../config/env';
import { MessagingSocketEvent, NewMessageSocketEvent } from '../../types/messaging.types';

type MessagingSocketHandlers = {
  onNewMessage: (event: NewMessageSocketEvent) => void;
  onStatusChange?: (status: MessagingSocketStatus) => void;
};

export type MessagingSocketStatus = 'disabled' | 'connecting' | 'connected' | 'authenticated' | 'closed' | 'error';

export type MessagingSocketConnection = {
  close: () => void;
};

export function connectMessagingSocket(token: string, handlers: MessagingSocketHandlers): MessagingSocketConnection {
  if (!config.websocketUrl) {
    handlers.onStatusChange?.('disabled');
    return { close: () => undefined };
  }

  handlers.onStatusChange?.('connecting');

  const socket = new WebSocket(config.websocketUrl);

  socket.onopen = () => {
    handlers.onStatusChange?.('connected');
    socket.send(JSON.stringify({ type: 'auth', token }));
  };

  socket.onmessage = (event) => {
    const message = parseSocketEvent(event.data);

    if (!message) {
      return;
    }

    if (message.type === 'authenticated') {
      handlers.onStatusChange?.('authenticated');
      return;
    }

    if (message.type === 'new_message') {
      handlers.onNewMessage(message);
    }
  };

  socket.onerror = () => {
    handlers.onStatusChange?.('error');
  };

  socket.onclose = () => {
    handlers.onStatusChange?.('closed');
  };

  return {
    close: () => socket.close(),
  };
}

function parseSocketEvent(data: unknown): MessagingSocketEvent | null {
  if (typeof data !== 'string') {
    return null;
  }

  try {
    const parsed = JSON.parse(data) as MessagingSocketEvent;
    return typeof parsed.type === 'string' ? parsed : null;
  } catch {
    return null;
  }
}
