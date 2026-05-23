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
        <View style={styles.queue}>
          <View style={styles.queueIcon}>
            <Ionicons
              color={report.status === 'open' ? theme.colors.danger : theme.colors.success}
              name={report.status === 'open' ? 'radio-button-on-outline' : 'checkmark-circle-outline'}
              size={20}
            />
          </View>
          <View style={styles.queueCopy}>
            <AppText style={styles.queueTitle}>
              {report.status === 'open' ? 'À traiter' : 'Signalement traité'}
            </AppText>
            <AppText variant="muted">#{report.id} · {formatDate(report.createdAt)}</AppText>
          </View>
        </View>
        <View style={styles.badges}>
          <StatusBadge
            label={report.status === 'open' ? 'Ouvert' : 'Résolu'}
            variant={report.status === 'open' ? 'danger' : 'success'}
          />
          <StatusBadge label={report.type === 'post' ? 'Post' : 'Commentaire'} variant="warning" />
        </View>
      </View>

      <View style={styles.copy}>
        <AppText style={styles.reason}>{labelForReason(report.reason)}</AppText>
        <AppText variant="muted">Signalé par @{report.reporter.username}</AppText>
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
          <AppText style={styles.resolutionTitle}>
            Décision : {labelForDecision(report.decision)}
          </AppText>
          {report.adminNote ? <AppText variant="muted">Note : {report.adminNote}</AppText> : null}
          <AppText variant="muted">
            Résolu le {formatDate(report.resolvedAt)}
            {report.resolvedBy ? ` par @${report.resolvedBy.username}` : ''}
          </AppText>
        </View>
      ) : null}

      {report.status === 'open' ? (
        <View style={styles.actionsPanel}>
          <Pressable
            accessibilityRole="button"
            disabled={resolving}
            onPress={confirmReject}
            style={({ pressed }) => [styles.resolveAction, pressed && styles.pressed, resolving && styles.disabled]}
          >
            <Ionicons color={theme.colors.accent} name="close-circle-outline" size={18} />
            <AppText style={styles.actionLabel}>Rejeter</AppText>
          </Pressable>
          <Pressable
            accessibilityRole="button"
            disabled={resolving}
            onPress={confirmRemoveContent}
            style={({ pressed }) => [styles.deleteAction, pressed && styles.pressed, resolving && styles.disabled]}
          >
            <Ionicons color={theme.colors.danger} name="trash-outline" size={18} />
            <AppText style={styles.actionLabel}>Supprimer et avertir</AppText>
          </Pressable>
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
    gap: theme.spacing.md,
  },
  queue: {
    alignItems: 'center',
    flexDirection: 'row',
    gap: theme.spacing.md,
    width: '100%',
  },
  queueIcon: {
    alignItems: 'center',
    backgroundColor: theme.colors.surfaceElevated,
    borderColor: theme.colors.borderSoft,
    borderRadius: theme.radius.full,
    borderWidth: 1,
    height: 38,
    justifyContent: 'center',
    width: 38,
  },
  queueCopy: {
    flex: 1,
    gap: theme.spacing.xs,
  },
  queueTitle: {
    fontSize: theme.typography.sizes.lg,
    fontWeight: theme.typography.weights.bold,
  },
  badges: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: theme.spacing.sm,
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
  reason: {
    fontSize: theme.typography.sizes.md,
    fontWeight: theme.typography.weights.bold,
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
    backgroundColor: theme.colors.surfaceElevated,
    borderColor: theme.colors.borderSoft,
    borderRadius: theme.radius.md,
    borderWidth: 1,
    gap: theme.spacing.xs,
    padding: theme.spacing.md,
  },
  resolutionTitle: {
    fontSize: theme.typography.sizes.sm,
    fontWeight: theme.typography.weights.bold,
  },
  actionsPanel: {
    flexDirection: 'row',
    gap: theme.spacing.md,
  },
  resolveAction: {
    alignItems: 'center',
    backgroundColor: theme.colors.accentSoft,
    borderColor: theme.colors.accent,
    borderRadius: theme.radius.md,
    borderWidth: 1,
    flex: 1,
    flexDirection: 'row',
    gap: theme.spacing.sm,
    justifyContent: 'center',
    minHeight: 46,
    paddingHorizontal: theme.spacing.sm,
  },
  deleteAction: {
    alignItems: 'center',
    backgroundColor: 'rgba(255, 59, 92, 0.1)',
    borderColor: theme.colors.danger,
    borderRadius: theme.radius.md,
    borderWidth: 1,
    flex: 1,
    flexDirection: 'row',
    gap: theme.spacing.sm,
    justifyContent: 'center',
    minHeight: 46,
    paddingHorizontal: theme.spacing.sm,
  },
  actionLabel: {
    flexShrink: 1,
    fontSize: theme.typography.sizes.sm,
    fontWeight: theme.typography.weights.bold,
    textAlign: 'center',
  },
});
