import { createNativeStackNavigator } from '@react-navigation/native-stack';

import { FeedStackParamList } from './navigation.types';
import { FeedScreen } from '../screens/Feed/FeedScreen';
import { PostDetailScreen } from '../screens/Feed/PostDetailScreen';

const Stack = createNativeStackNavigator<FeedStackParamList>();

export function FeedStackNavigator() {
  return (
    <Stack.Navigator screenOptions={{ headerShown: false }}>
      <Stack.Screen component={FeedScreen} name="Feed" />
      <Stack.Screen component={PostDetailScreen} name="PostDetail" />
    </Stack.Navigator>
  );
}
