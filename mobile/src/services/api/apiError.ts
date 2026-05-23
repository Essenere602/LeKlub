import { AxiosError } from 'axios';

import { ApiError, ApiResponse } from '../../types/api.types';

export function toApiError(error: unknown): ApiError {
  if (error instanceof AxiosError) {
    if (!error.response) {
      return {
        message: "Impossible de joindre l'API LeKlub. Vérifiez l'URL API et le réseau.",
      };
    }

    const data = error.response.data as ApiResponse<unknown> | undefined;
    const validationMessage = formatValidationErrors(data?.errors);

    return {
      message: validationMessage ?? translateMessage(data?.message ?? messageForStatus(error.response.status)),
      errors: data?.errors,
      status: error.response.status,
    };
  }

  if (error instanceof Error) {
    return { message: translateMessage(error.message) };
  }

  return { message: 'Une erreur inattendue est survenue.' };
}

function formatValidationErrors(errors: ApiResponse<unknown>['errors'] | undefined): string | null {
  if (!errors || Array.isArray(errors)) {
    return null;
  }

  const messages = Object.entries(errors)
    .flatMap(([field, fieldErrors]) => fieldErrors.map((message) => `${labelForField(field)} : ${translateMessage(message)}`));

  return messages.length > 0 ? messages.join('\n') : null;
}

function translateMessage(message: string): string {
  const translations: Record<string, string> = {
    'This value is not a valid email address.': "l'adresse email est invalide.",
    'This value is too short. It should have 3 characters or more.': 'minimum 3 caractères.',
    'This value is too short. It should have 10 characters or more.': 'minimum 10 caractères.',
    'Password must contain at least one lowercase letter, one uppercase letter and one number.':
      'doit contenir au moins une minuscule, une majuscule et un chiffre.',
    'Password confirmation does not match.': 'la confirmation ne correspond pas au nouveau mot de passe.',
    'Unable to update password.': 'Impossible de mettre à jour le mot de passe.',
    'Unable to update account.': 'Impossible de mettre à jour le compte.',
    'Email already used.': 'Cet email est déjà utilisé.',
    'Username already used.': 'Ce nom utilisateur est déjà utilisé.',
    'Username can only contain letters, numbers and underscores.':
      'lettres, chiffres et underscore uniquement.',
    'Football data is temporarily unavailable. Please try again later.':
      'Les données football sont temporairement indisponibles. Réessayez dans un instant.',
    'Competition not supported.':
      "Cette compétition n'est pas supportée par LeKlub.",
    'Content already reported by this user.':
      'Ce contenu a déjà été signalé avec votre compte.',
    'Invalid report status.':
      'Le filtre de signalement est invalide.',
    'Report already resolved.':
      'Ce signalement a déjà été traité.',
    'Reported content is no longer available.':
      "Le contenu signalé n'est plus disponible.",
    'Account temporarily suspended.':
      'Votre compte est temporairement suspendu pour les actions de publication.',
  };

  return translations[message] ?? message;
}

function labelForField(field: string): string {
  const labels: Record<string, string> = {
    email: 'Email',
    username: 'Nom utilisateur',
    password: 'Mot de passe',
    currentPassword: 'Ancien mot de passe',
    newPassword: 'Nouveau mot de passe',
    newPasswordConfirmation: 'Confirmation',
    displayName: 'Nom affiché',
    bio: 'Bio',
    favoriteTeamName: 'Équipe favorite',
    avatarUrl: 'URL avatar',
    content: 'Message',
    reason: 'Raison',
    details: 'Précision',
    recipientId: 'Destinataire',
  };

  return labels[field] ?? field;
}

function messageForStatus(status: number): string {
  if (status === 401) {
    return 'Identifiants invalides ou session expirée.';
  }

  if (status === 409) {
    return 'Cet email ou ce nom utilisateur est déjà utilisé.';
  }

  if (status === 422) {
    return 'Certaines informations sont invalides.';
  }

  return 'Une erreur est survenue. Réessayez dans un instant.';
}
