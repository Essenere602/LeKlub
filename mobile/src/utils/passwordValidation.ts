export type PasswordRule = {
  id: 'minLength' | 'maxLength' | 'lowercase' | 'uppercase' | 'number';
  label: string;
  isValid: (password: string) => boolean;
};

export const passwordRules: PasswordRule[] = [
  {
    id: 'minLength',
    label: '10 caractères minimum',
    isValid: (password) => password.length >= 10,
  },
  {
    id: 'maxLength',
    label: '128 caractères maximum',
    isValid: (password) => password.length <= 128,
  },
  {
    id: 'lowercase',
    label: 'Une lettre minuscule',
    isValid: (password) => /[a-z]/.test(password),
  },
  {
    id: 'uppercase',
    label: 'Une lettre majuscule',
    isValid: (password) => /[A-Z]/.test(password),
  },
  {
    id: 'number',
    label: 'Un chiffre',
    isValid: (password) => /\d/.test(password),
  },
];

export function isPasswordValid(password: string): boolean {
  return passwordRules.every((rule) => rule.isValid(password));
}

export function passwordConfirmationMatches(password: string, confirmation: string): boolean {
  return password === confirmation;
}
