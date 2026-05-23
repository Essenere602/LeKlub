import { MainTabsNavigator } from './MainTabsNavigator';
import { MessagingUnreadProvider } from '../contexts/MessagingUnreadContext';

export function MainNavigator() {
  return (
    <MessagingUnreadProvider>
      <MainTabsNavigator />
    </MessagingUnreadProvider>
  );
}
