import { ComponentProps, PropsWithChildren } from 'react';
import { StyleSheet, View } from 'react-native';
import { Ionicons } from '@expo/vector-icons';

import { theme } from '../../config/theme';
import { AppText } from '../ui/AppText';

type FootballShellProps = PropsWithChildren<{
  icon?: ComponentProps<typeof Ionicons>['name'];
  subtitle?: string;
  title: string;
}>;

export function FootballShell({ children, icon = 'football-outline', subtitle, title }: FootballShellProps) {
  return (
    <View style={styles.container}>
      <View style={styles.header}>
        <View style={styles.headerContent}>
          <Ionicons color={theme.colors.football.neon} name={icon} size={30} />
          <View style={styles.titleBlock}>
            <AppText style={styles.title}>{title}</AppText>
            {subtitle ? <AppText style={styles.subtitle}>{subtitle}</AppText> : null}
          </View>
        </View>
      </View>
      {children}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    backgroundColor: theme.colors.football.blackPure,
    flex: 1,
  },
  header: {
    backgroundColor: theme.colors.football.black,
    borderBottomColor: theme.colors.football.neon,
    borderBottomLeftRadius: 24,
    borderBottomRightRadius: 24,
    borderBottomWidth: 2,
    paddingBottom: theme.spacing['2xl'],
    paddingHorizontal: theme.spacing.lg,
    paddingTop: theme.spacing.xl,
  },
  headerContent: {
    alignItems: 'center',
    flexDirection: 'row',
    gap: theme.spacing.md,
  },
  titleBlock: {
    flex: 1,
    gap: theme.spacing.sm,
  },
  title: {
    alignSelf: 'flex-start',
    borderBottomColor: theme.colors.football.neon,
    borderBottomWidth: 3,
    color: theme.colors.text.primary,
    fontSize: 30,
    fontWeight: theme.typography.weights.bold,
    lineHeight: 36,
    paddingBottom: theme.spacing.sm,
  },
  subtitle: {
    color: theme.colors.text.secondary,
    fontSize: theme.typography.sizes.md,
    lineHeight: 24,
  },
});
