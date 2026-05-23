import { StyleSheet, View } from 'react-native';
import { Ionicons } from '@expo/vector-icons';

import { theme } from '../../config/theme';
import { AppCard } from '../ui/AppCard';
import { AppText } from '../ui/AppText';

type AdminStatCardProps = {
  icon: keyof typeof Ionicons.glyphMap;
  label: string;
  value: number;
  priority?: 'normal' | 'critical';
};

export function AdminStatCard({ icon, label, priority = 'normal', value }: AdminStatCardProps) {
  return (
    <AppCard style={[styles.card, priority === 'critical' && styles.criticalCard]}>
      <View style={[styles.icon, priority === 'critical' && styles.criticalIcon]}>
        <Ionicons color={priority === 'critical' ? theme.colors.text.inverse : theme.colors.accent} name={icon} size={20} />
      </View>
      <View style={styles.copy}>
        <AppText style={[styles.value, priority === 'critical' && styles.criticalValue]}>{value}</AppText>
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
  criticalCard: {
    backgroundColor: theme.colors.surfaceElevated,
    borderColor: theme.colors.accentGlow,
  },
  icon: {
    alignItems: 'center',
    backgroundColor: theme.colors.accentSoft,
    borderRadius: theme.radius.md,
    height: 36,
    justifyContent: 'center',
    width: 36,
  },
  criticalIcon: {
    backgroundColor: theme.colors.accent,
  },
  copy: {
    gap: theme.spacing.xs,
  },
  value: {
    color: theme.colors.accent,
    fontSize: theme.typography.sizes.xl,
    fontWeight: theme.typography.weights.bold,
  },
  criticalValue: {
    fontSize: theme.typography.sizes['2xl'],
  },
});
