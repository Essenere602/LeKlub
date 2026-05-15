import { ApiResponse } from '../../types/api.types';
import { Conversation, PrivateMessage } from '../../types/messaging.types';
import { apiClient } from '../api/apiClient';

type ConversationsResponseData = {
  conversations: Conversation[];
};

type ConversationResponseData = {
  conversation: Conversation;
};

type MessagesResponseData = {
  messages: PrivateMessage[];
};

type MessageResponseData = {
  message: PrivateMessage;
};

export const messagingService = {
  async listConversations(): Promise<Conversation[]> {
    const response = await apiClient.get<ApiResponse<ConversationsResponseData>>('/conversations');

    if (!response.data.data?.conversations) {
      throw new Error(response.data.message ?? 'Unable to load conversations.');
    }

    return response.data.data.conversations;
  },

  async createOrOpenConversation(recipientId: number): Promise<Conversation> {
    const response = await apiClient.post<ApiResponse<ConversationResponseData>>('/conversations', {
      recipientId,
    });

    if (!response.data.data?.conversation) {
      throw new Error(response.data.message ?? 'Unable to create conversation.');
    }

    return response.data.data.conversation;
  },

  async listMessages(conversationId: number): Promise<PrivateMessage[]> {
    const response = await apiClient.get<ApiResponse<MessagesResponseData>>(`/conversations/${conversationId}/messages`);

    if (!response.data.data?.messages) {
      throw new Error(response.data.message ?? 'Unable to load messages.');
    }

    return response.data.data.messages;
  },

  async sendMessage(conversationId: number, content: string): Promise<PrivateMessage> {
    const response = await apiClient.post<ApiResponse<MessageResponseData>>(`/conversations/${conversationId}/messages`, {
      content,
    });

    if (!response.data.data?.message) {
      throw new Error(response.data.message ?? 'Unable to send message.');
    }

    return response.data.data.message;
  },

  async markAsRead(conversationId: number): Promise<void> {
    await apiClient.patch<ApiResponse<Record<string, never>>>(`/conversations/${conversationId}/read`);
  },
};
