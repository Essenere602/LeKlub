import { StyleSheet, View } from 'react-native';
import { Ionicons } from '@expo/vector-icons';

import { theme } from '../../config/theme';
import { AppCard } from '../ui/AppCard';
import { AppText } from '../ui/AppText';

type AdminStatCardProps = {
  icon: keyof typeof Ionicons.glyphMap;
  label: string;
  value: number;
};

export function AdminStatCard({ icon, label, value }: AdminStatCardProps) {
  return (
    <AppCard style={styles.card}>
      <View style={styles.icon}>
        <Ionicons color={theme.colors.accent} name={icon} size={20} />
      </View>
      <View style={styles.copy}>
        <AppText style={styles.value}>{value}</AppText>
        <AppText variant="muted">{label}</AppText>
      </View>
    </AppCard>
  );
}

const styles = StyleSheet.create({
  card: {
    flex: 1,
    gap: theme.spacing.md,
    minWidth: 140,
  },
  icon: {
    alignItems: 'center',
    backgroundColor: theme.colors.accentSoft,
    borderRadius: theme.radius.md,
    height: 36,
    justifyContent: 'center',
    width: 36,
  },
  copy: {
    gap: theme.spacing.xs,
  },
  value: {
    color: theme.colors.accent,
    fontSize: theme.typography.sizes.xl,
    fontWeight: theme.typography.weights.bold,
  },
});
