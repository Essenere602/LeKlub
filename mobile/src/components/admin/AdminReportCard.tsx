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
  onReject: () => void;
  onRemoveContent: () => void;
};

export function AdminReportCard({ onReject, onRemoveContent, report, resolving = false }: AdminReportCardProps) {
  function confirmReject() {
    Alert.alert(
      'Rejeter le signalement',
      'Le contenu restera visible et le signalement sera marqué comme résolu.',
      [
        { text: 'Annuler', style: 'cancel' },
        { text: 'Rejeter', onPress: onReject },
      ],
    );
  }

  function confirmRemoveContent() {
    Alert.alert(
      'Supprimer et avertir',
      "Le contenu sera supprimé logiquement, l'auteur recevra un avertissement et pourra être suspendu après 3 avertissements.",
      [
        { text: 'Annuler', style: 'cancel' },
        { text: 'Supprimer', onPress: onRemoveContent, style: 'destructive' },
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
          <View style={styles.headerActions}>
            <Pressable
              accessibilityRole="button"
              disabled={resolving}
              onPress={confirmReject}
              style={({ pressed }) => [styles.resolveButton, pressed && styles.pressed, resolving && styles.disabled]}
            >
              <Ionicons color={theme.colors.accent} name="close-circle-outline" size={18} />
            </Pressable>
            <Pressable
              accessibilityRole="button"
              disabled={resolving}
              onPress={confirmRemoveContent}
              style={({ pressed }) => [styles.deleteButton, pressed && styles.pressed, resolving && styles.disabled]}
            >
              <Ionicons color={theme.colors.danger} name="trash-outline" size={18} />
            </Pressable>
          </View>
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
        <View style={styles.resolution}>
          <AppText variant="muted">
            Décision : {labelForDecision(report.decision)}
          </AppText>
          {report.adminNote ? <AppText variant="muted">Note : {report.adminNote}</AppText> : null}
          <AppText variant="muted">
            Résolu le {formatDate(report.resolvedAt)}
            {report.resolvedBy ? ` par @${report.resolvedBy.username}` : ''}
          </AppText>
        </View>
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

function labelForDecision(decision: AdminReport['decision']): string {
  if (decision === 'rejected') {
    return 'Signalement rejeté';
  }

  if (decision === 'content_removed') {
    return 'Contenu supprimé';
  }

  return 'Non renseignée';
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
  headerActions: {
    flexDirection: 'row',
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
  deleteButton: {
    alignItems: 'center',
    backgroundColor: 'rgba(255, 59, 92, 0.1)',
    borderColor: theme.colors.danger,
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
  resolution: {
    borderTopColor: theme.colors.border,
    borderTopWidth: 1,
    gap: theme.spacing.xs,
    paddingTop: theme.spacing.md,
  },
});
