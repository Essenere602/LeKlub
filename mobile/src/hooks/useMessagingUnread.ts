import { useContext } from 'react';

import { MessagingUnreadContext } from '../contexts/MessagingUnreadContext';

export function useMessagingUnread() {
  const context = useContext(MessagingUnreadContext);

  if (!context) {
    throw new Error('useMessagingUnread must be used within MessagingUnreadProvider.');
  }

  return context;
}
