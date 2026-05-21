import { StyleSheet, View } from 'react-native';

import { theme } from '../../config/theme';
import { AdminWarning } from '../../types/admin.types';
import { AppCard } from '../ui/AppCard';
import { AppText } from '../ui/AppText';
import { StatusBadge } from '../ui/StatusBadge';

type AdminWarningCardProps = {
  warning: AdminWarning;
};

export function AdminWarningCard({ warning }: AdminWarningCardProps) {
  return (
    <AppCard style={styles.card}>
      <View style={styles.header}>
        <View style={styles.titleGroup}>
          <AppText variant="label">@{warning.user.username}</AppText>
          <AppText variant="muted">
            {warning.warningCount}/3 avertissements · {formatDate(warning.createdAt)}
          </AppText>
        </View>
        <StatusBadge label={statusLabel(warning)} variant={statusVariant(warning)} />
      </View>

      <View style={styles.warningBox}>
        <AppText variant="muted">Raison</AppText>
        <AppText style={styles.reason}>{warning.reason ?? 'Aucune note admin.'}</AppText>
      </View>

      <View style={styles.metaGrid}>
        <Meta label="Contenu" value={`${warning.contentType === 'post' ? 'Post' : 'Commentaire'} #${warning.contentId}`} />
        <Meta label="Modérateur" value={`@${warning.createdBy.username}`} />
        <Meta label="Signalement" value={`#${warning.report.id ?? '-'} · ${labelForReportReason(warning.report.reason)}`} />
        <Meta label="Décision" value={labelForDecision(warning.report.decision)} />
      </View>

      {warning.isSuspended && warning.suspendedUntil ? (
        <View style={styles.suspension}>
          <AppText variant="label">Suspension active</AppText>
          <AppText variant="muted">Jusqu'au {formatDate(warning.suspendedUntil)}</AppText>
        </View>
      ) : null}
    </AppCard>
  );
}

type MetaProps = {
  label: string;
  value: string;
};

function Meta({ label, value }: MetaProps) {
  return (
    <View style={styles.metaItem}>
      <AppText variant="muted">{label}</AppText>
      <AppText style={styles.metaValue}>{value}</AppText>
    </View>
  );
}

function statusLabel(warning: AdminWarning): string {
  if (warning.isSuspended) {
    return 'Suspendu';
  }

  if (warning.warningCount >= 2) {
    return 'Proche du seuil';
  }

  return 'Averti';
}

function statusVariant(warning: AdminWarning): 'danger' | 'warning' | 'neutral' {
  if (warning.isSuspended) {
    return 'danger';
  }

  if (warning.warningCount >= 2) {
    return 'warning';
  }

  return 'neutral';
}

function labelForReportReason(reason: string): string {
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

function labelForDecision(decision: AdminWarning['report']['decision']): string {
  if (decision === 'content_removed') {
    return 'Contenu supprimé';
  }

  if (decision === 'rejected') {
    return 'Rejeté';
  }

  return 'Non résolu';
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
  titleGroup: {
    flex: 1,
    gap: theme.spacing.xs,
  },
  warningBox: {
    backgroundColor: theme.colors.surfaceElevated,
    borderColor: theme.colors.border,
    borderRadius: theme.radius.md,
    borderWidth: 1,
    gap: theme.spacing.xs,
    padding: theme.spacing.md,
  },
  reason: {
    fontSize: theme.typography.sizes.sm,
    lineHeight: 20,
  },
  metaGrid: {
    gap: theme.spacing.sm,
  },
  metaItem: {
    gap: theme.spacing.xs,
  },
  metaValue: {
    fontSize: theme.typography.sizes.sm,
    lineHeight: 20,
  },
  suspension: {
    borderColor: theme.colors.danger,
    borderRadius: theme.radius.md,
    borderWidth: 1,
    gap: theme.spacing.xs,
    padding: theme.spacing.md,
  },
});
