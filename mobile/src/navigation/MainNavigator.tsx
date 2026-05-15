import { createNativeStackNavigator } from '@react-navigation/native-stack';

import { MainStackParamList } from './navigation.types';
import { FeedScreen } from '../screens/Feed/FeedScreen';
import { FootballCompetitionScreen } from '../screens/Football/FootballCompetitionScreen';
import { FootballHomeScreen } from '../screens/Football/FootballHomeScreen';
import { HomeScreen } from '../screens/Home/HomeScreen';
import { ConversationDetailScreen } from '../screens/Messaging/ConversationDetailScreen';
import { ConversationListScreen } from '../screens/Messaging/ConversationListScreen';
import { UserPickerScreen } from '../screens/Messaging/UserPickerScreen';
import { PostDetailScreen } from '../screens/Feed/PostDetailScreen';
import { ProfileScreen } from '../screens/Profile/ProfileScreen';

const Stack = createNativeStackNavigator<MainStackParamList>();

export function MainNavigator() {
  return (
    <Stack.Navigator screenOptions={{ headerShown: false }}>
      <Stack.Screen component={HomeScreen} name="Home" />
      <Stack.Screen component={ProfileScreen} name="Profile" />
      <Stack.Screen component={FeedScreen} name="Feed" />
      <Stack.Screen component={PostDetailScreen} name="PostDetail" />
      <Stack.Screen component={FootballHomeScreen} name="Football" />
      <Stack.Screen component={FootballCompetitionScreen} name="FootballCompetition" />
      <Stack.Screen component={ConversationListScreen} name="Conversations" />
      <Stack.Screen component={UserPickerScreen} name="UserPicker" />
      <Stack.Screen component={ConversationDetailScreen} name="ConversationDetail" />
    </Stack.Navigator>
  );
}
