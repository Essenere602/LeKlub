import { MainTabsNavigator } from './MainTabsNavigator';
import { MessagingSocketProvider } from '../contexts/MessagingSocketContext';
import { MessagingUnreadProvider } from '../contexts/MessagingUnreadContext';

export function MainNavigator() {
  return (
    <MessagingSocketProvider>
      <MessagingUnreadProvider>
        <MainTabsNavigator />
      </MessagingUnreadProvider>
    </MessagingSocketProvider>
  );
}
