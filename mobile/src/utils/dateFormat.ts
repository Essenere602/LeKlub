type DateInput = string | null | undefined;

export function formatShortDate(value: DateInput, fallback = '-'): string {
  const date = parseDate(value);

  if (!date) {
    return fallback;
  }

  return new Intl.DateTimeFormat('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
  }).format(date);
}

export function formatShortDateTime(value: DateInput, fallback?: string): string {
  const date = parseDate(value);

  if (!date) {
    return fallback ?? (value ?? '');
  }

  return date.toLocaleDateString('fr-FR', {
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
    month: 'short',
  });
}

export function formatFootballDateTime(value: DateInput, fallback = 'Date non communiquée'): string {
  const date = parseDate(value);

  if (!date) {
    return fallback;
  }

  return date.toLocaleDateString('fr-FR', {
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
    month: 'short',
    weekday: 'short',
  });
}

export function formatMessageTime(value: DateInput): string {
  const date = parseDate(value);

  if (!date) {
    return '';
  }

  return date.toLocaleTimeString('fr-FR', {
    hour: '2-digit',
    minute: '2-digit',
  });
}

function parseDate(value: DateInput): Date | null {
  if (!value) {
    return null;
  }

  const date = new Date(value);

  return Number.isNaN(date.getTime()) ? null : date;
}
