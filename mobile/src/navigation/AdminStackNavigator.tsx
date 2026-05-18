import { createNativeStackNavigator } from '@react-navigation/native-stack';

import { AdminStackParamList } from './navigation.types';
import { AdminFeedModerationScreen } from '../screens/Admin/AdminFeedModerationScreen';
import { AdminHomeScreen } from '../screens/Admin/AdminHomeScreen';
import { AdminUsersScreen } from '../screens/Admin/AdminUsersScreen';

const Stack = createNativeStackNavigator<AdminStackParamList>();

export function AdminStackNavigator() {
  return (
    <Stack.Navigator screenOptions={{ headerShown: false }}>
      <Stack.Screen component={AdminHomeScreen} name="AdminHome" />
      <Stack.Screen component={AdminUsersScreen} name="AdminUsers" />
      <Stack.Screen component={AdminFeedModerationScreen} name="AdminFeedModeration" />
    </Stack.Navigator>
  );
}
