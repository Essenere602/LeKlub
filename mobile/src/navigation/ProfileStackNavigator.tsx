import { createNativeStackNavigator } from '@react-navigation/native-stack';

import { ProfileStackParamList } from './navigation.types';
import { ChangePasswordScreen } from '../screens/Profile/ChangePasswordScreen';
import { EditAccountScreen } from '../screens/Profile/EditAccountScreen';
import { EditProfileScreen } from '../screens/Profile/EditProfileScreen';
import { NotificationsScreen } from '../screens/Profile/NotificationsScreen';
import { ProfileScreen } from '../screens/Profile/ProfileScreen';

const Stack = createNativeStackNavigator<ProfileStackParamList>();

export function ProfileStackNavigator() {
  return (
    <Stack.Navigator screenOptions={{ headerShown: false }}>
      <Stack.Screen component={ProfileScreen} name="Profile" />
      <Stack.Screen component={EditAccountScreen} name="EditAccount" />
      <Stack.Screen component={EditProfileScreen} name="EditProfile" />
      <Stack.Screen component={ChangePasswordScreen} name="ChangePassword" />
      <Stack.Screen component={NotificationsScreen} name="Notifications" />
    </Stack.Navigator>
  );
}
