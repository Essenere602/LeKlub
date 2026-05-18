import { createNativeStackNavigator } from '@react-navigation/native-stack';

import { FootballStackParamList } from './navigation.types';
import { FootballCompetitionScreen } from '../screens/Football/FootballCompetitionScreen';
import { FootballHomeScreen } from '../screens/Football/FootballHomeScreen';

const Stack = createNativeStackNavigator<FootballStackParamList>();

export function FootballStackNavigator() {
  return (
    <Stack.Navigator screenOptions={{ headerShown: false }}>
      <Stack.Screen component={FootballHomeScreen} name="Football" />
      <Stack.Screen component={FootballCompetitionScreen} name="FootballCompetition" />
    </Stack.Navigator>
  );
}
