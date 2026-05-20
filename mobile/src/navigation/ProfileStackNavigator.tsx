import { createNativeStackNavigator } from '@react-navigation/native-stack';

import { ProfileStackParamList } from './navigation.types';
import { ChangePasswordScreen } from '../screens/Profile/ChangePasswordScreen';
import { ProfileScreen } from '../screens/Profile/ProfileScreen';

const Stack = createNativeStackNavigator<ProfileStackParamList>();

export function ProfileStackNavigator() {
  return (
    <Stack.Navigator screenOptions={{ headerShown: false }}>
      <Stack.Screen component={ProfileScreen} name="Profile" />
      <Stack.Screen component={ChangePasswordScreen} name="ChangePassword" />
    </Stack.Navigator>
  );
}
