import { Alert, Pressable, StyleSheet, View } from 'react-native';
import { Ionicons } from '@expo/vector-icons';

import { theme } from '../../config/theme';
import { AdminReport } from '../../types/admin.types';
import { AppCard } from '../ui/AppCard';
import { AppText } from '../ui/AppText';
import { StatusBadge } from '../ui/StatusBadge';

type AdminReportCardProps = {
  report: AdminReport;
  resolving?: boolean;
  onResolve: () => void;
};

export function AdminReportCard({ onResolve, report, resolving = false }: AdminReportCardProps) {
  function confirmResolve() {
    Alert.alert(
      'Résoudre le signalement',
      'Le contenu ne sera pas supprimé automatiquement. La modération reste une action séparée.',
      [
        { text: 'Annuler', style: 'cancel' },
        { text: 'Résoudre', onPress: onResolve },
      ],
    );
  }

  return (
    <AppCard style={styles.card}>
      <View style={styles.header}>
        <View style={styles.badges}>
          <StatusBadge label={report.type === 'post' ? 'Post' : 'Commentaire'} variant="warning" />
          <StatusBadge
            label={report.status === 'open' ? 'Ouvert' : 'Résolu'}
            variant={report.status === 'open' ? 'danger' : 'success'}
          />
        </View>
        {report.status === 'open' ? (
          <Pressable
            accessibilityRole="button"
            disabled={resolving}
            onPress={confirmResolve}
            style={({ pressed }) => [styles.resolveButton, pressed && styles.pressed, resolving && styles.disabled]}
          >
            <Ionicons color={theme.colors.accent} name="checkmark-done-outline" size={18} />
          </Pressable>
        ) : null}
      </View>

      <View style={styles.copy}>
        <AppText variant="label">{labelForReason(report.reason)}</AppText>
        <AppText variant="muted">Signalé par @{report.reporter.username} · {formatDate(report.createdAt)}</AppText>
      </View>

      {report.details ? <AppText style={styles.details}>{report.details}</AppText> : null}

      <View style={styles.context}>
        <AppText variant="muted">Contenu signalé</AppText>
        <AppText style={styles.excerpt}>{report.content.excerpt ?? 'Contenu indisponible'}</AppText>
        {report.content.author ? <AppText variant="muted">Auteur : @{report.content.author.username}</AppText> : null}
        {report.content.deletedAt ? <AppText variant="muted">Déjà supprimé logiquement.</AppText> : null}
      </View>

      {report.resolvedAt ? (
        <AppText variant="muted">
          Résolu le {formatDate(report.resolvedAt)}
          {report.resolvedBy ? ` par @${report.resolvedBy.username}` : ''}
        </AppText>
      ) : null}
    </AppCard>
  );
}

function labelForReason(reason: string): string {
  const labels: Record<string, string> = {
    spam: 'Spam',
    insults: 'Insultes',
    harassment: 'Harcèlement',
    hate_content: 'Contenu haineux',
    inappropriate_content: 'Contenu inapproprié',
    other: 'Autre',
  };

  return labels[reason] ?? reason;
}

function formatDate(value: string): string {
  const date = new Date(value);

  if (Number.isNaN(date.getTime())) {
    return value;
  }

  return date.toLocaleDateString('fr-FR', {
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
    month: 'short',
  });
}

const styles = StyleSheet.create({
  card: {
    gap: theme.spacing.md,
  },
  header: {
    alignItems: 'flex-start',
    flexDirection: 'row',
    gap: theme.spacing.md,
    justifyContent: 'space-between',
  },
  badges: {
    flex: 1,
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: theme.spacing.sm,
  },
  resolveButton: {
    alignItems: 'center',
    backgroundColor: theme.colors.accentSoft,
    borderColor: theme.colors.accent,
    borderRadius: theme.radius.md,
    borderWidth: 1,
    height: 40,
    justifyContent: 'center',
    width: 40,
  },
  pressed: {
    opacity: 0.76,
  },
  disabled: {
    opacity: 0.45,
  },
  copy: {
    gap: theme.spacing.xs,
  },
  details: {
    fontSize: theme.typography.sizes.md,
    lineHeight: 23,
  },
  context: {
    backgroundColor: theme.colors.surfaceElevated,
    borderColor: theme.colors.border,
    borderRadius: theme.radius.md,
    borderWidth: 1,
    gap: theme.spacing.xs,
    padding: theme.spacing.md,
  },
  excerpt: {
    fontSize: theme.typography.sizes.sm,
    lineHeight: 20,
  },
});
