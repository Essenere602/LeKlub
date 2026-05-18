import { createNativeStackNavigator } from '@react-navigation/native-stack';

import { MessagingStackParamList } from './navigation.types';
import { ConversationDetailScreen } from '../screens/Messaging/ConversationDetailScreen';
import { ConversationListScreen } from '../screens/Messaging/ConversationListScreen';
import { UserPickerScreen } from '../screens/Messaging/UserPickerScreen';

const Stack = createNativeStackNavigator<MessagingStackParamList>();

export function MessagingStackNavigator() {
  return (
    <Stack.Navigator screenOptions={{ headerShown: false }}>
      <Stack.Screen component={ConversationListScreen} name="Conversations" />
      <Stack.Screen component={UserPickerScreen} name="UserPicker" />
      <Stack.Screen component={ConversationDetailScreen} name="ConversationDetail" />
    </Stack.Navigator>
  );
}
