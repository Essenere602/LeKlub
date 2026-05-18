import { Ionicons } from '@expo/vector-icons';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';

import { MainTabParamList } from './navigation.types';
import { AdminStackNavigator } from './AdminStackNavigator';
import { FeedStackNavigator } from './FeedStackNavigator';
import { FootballStackNavigator } from './FootballStackNavigator';
import { MessagingStackNavigator } from './MessagingStackNavigator';
import { theme } from '../config/theme';
import { useAuth } from '../hooks/useAuth';
import { HomeScreen } from '../screens/Home/HomeScreen';
import { ProfileScreen } from '../screens/Profile/ProfileScreen';

const Tab = createBottomTabNavigator<MainTabParamList>();

export function MainTabsNavigator() {
  const { user } = useAuth();
  const isAdmin = user?.roles.includes('ROLE_ADMIN') ?? false;

  return (
    <Tab.Navigator
      screenOptions={({ route }) => ({
        headerShown: false,
        tabBarActiveTintColor: theme.colors.accent,
        tabBarInactiveTintColor: theme.colors.text.muted,
        tabBarLabelStyle: {
          fontSize: 11,
          fontWeight: theme.typography.weights.semibold,
        },
        tabBarStyle: {
          backgroundColor: theme.colors.football.black,
          borderTopColor: theme.colors.border,
          borderTopWidth: 1,
          minHeight: 62,
          paddingBottom: 8,
          paddingTop: 6,
        },
        tabBarIcon: ({ color, size }) => (
          <Ionicons color={color} name={iconForRoute(route.name)} size={size} />
        ),
      })}
    >
      <Tab.Screen component={HomeScreen} name="Home" options={{ title: 'Accueil' }} />
      <Tab.Screen component={FeedStackNavigator} name="FeedTab" options={{ title: 'Feed' }} />
      <Tab.Screen component={FootballStackNavigator} name="FootballTab" options={{ title: 'Football' }} />
      <Tab.Screen component={MessagingStackNavigator} name="MessagingTab" options={{ title: 'Messages' }} />
      <Tab.Screen component={ProfileScreen} name="ProfileTab" options={{ title: 'Profil' }} />
      {isAdmin ? <Tab.Screen component={AdminStackNavigator} name="AdminTab" options={{ title: 'Admin' }} /> : null}
    </Tab.Navigator>
  );
}

function iconForRoute(routeName: keyof MainTabParamList): keyof typeof Ionicons.glyphMap {
  const icons: Record<keyof MainTabParamList, keyof typeof Ionicons.glyphMap> = {
    FeedTab: 'chatbubbles-outline',
    FootballTab: 'football-outline',
    Home: 'home-outline',
    MessagingTab: 'mail-outline',
    ProfileTab: 'person-outline',
    AdminTab: 'shield-checkmark-outline',
  };

  return icons[routeName];
}
