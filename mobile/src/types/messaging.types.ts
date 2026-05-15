export type MessageSender = {
  id: number;
  username: string;
};

export type PrivateMessage = {
  id: number;
  content: string;
  sender: MessageSender;
  readAt: string | null;
  createdAt: string;
};

export type ConversationParticipant = {
  id: number;
  username: string;
};

export type Conversation = {
  id: number;
  participant: ConversationParticipant | null;
  lastMessage: PrivateMessage | null;
  unreadCount: number;
  updatedAt: string;
};

export type NewMessageSocketEvent = {
  type: 'new_message';
  recipientId: number;
  conversationId: number;
  message: {
    id: number;
    sender: MessageSender;
    createdAt: string;
  };
};

export type MessagingSocketEvent =
  | { type: 'connected' }
  | { type: 'authenticated'; userId: number }
  | { type: 'error'; message: string }
  | { type: 'pong' }
  | NewMessageSocketEvent;
