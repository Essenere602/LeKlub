import { NativeStackScreenProps } from '@react-navigation/native-stack';
import { useState } from 'react';
import { KeyboardAvoidingView, Platform, StyleSheet, View } from 'react-native';

import { PasswordRulesChecklist } from '../../components/auth/PasswordRulesChecklist';
import { AppButton } from '../../components/ui/AppButton';
import { AppCard } from '../../components/ui/AppCard';
import { AppInput } from '../../components/ui/AppInput';
import { AppText } from '../../components/ui/AppText';
import { ErrorMessage } from '../../components/ui/ErrorMessage';
import { Screen } from '../../components/ui/Screen';
import { theme } from '../../config/theme';
import { AuthStackParamList } from '../../navigation/navigation.types';
import { toApiError } from '../../services/api/apiError';
import { authService } from '../../services/auth/authService';
import { isPasswordValid, passwordConfirmationMatches } from '../../utils/passwordValidation';

type ResetPasswordScreenProps = NativeStackScreenProps<AuthStackParamList, 'ResetPassword'>;

export function ResetPasswordScreen({ navigation }: ResetPasswordScreenProps) {
  const [token, setToken] = useState('');
  const [newPassword, setNewPassword] = useState('');
  const [newPasswordConfirmation, setNewPasswordConfirmation] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [successMessage, setSuccessMessage] = useState<string | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);

  async function submitReset() {
    setError(null);
    setSuccessMessage(null);

    if (!token.trim() || !newPassword || !newPasswordConfirmation) {
      setError('Token, nouveau mot de passe et confirmation sont requis.');
      return;
    }

    if (!isPasswordValid(newPassword)) {
      setError('Le nouveau mot de passe ne respecte pas encore toutes les règles.');
      return;
    }

    if (!passwordConfirmationMatches(newPassword, newPasswordConfirmation)) {
      setError('La confirmation ne correspond pas au nouveau mot de passe.');
      return;
    }

    try {
      setIsSubmitting(true);
      await authService.resetPassword({
        token: token.trim(),
        newPassword,
        newPasswordConfirmation,
      });
      setSuccessMessage('Mot de passe réinitialisé. Tu peux te connecter.');
    } catch (caughtError) {
      setError(toApiError(caughtError).message);
    } finally {
      setIsSubmitting(false);
    }
  }

  return (
    <Screen>
      <KeyboardAvoidingView behavior={Platform.OS === 'ios' ? 'padding' : undefined} style={styles.container}>
        <View style={styles.header}>
          <AppText style={styles.kicker}>Reset</AppText>
          <AppText variant="title">Nouveau mot de passe</AppText>
          <AppText variant="subtitle">Colle le token reçu dans Mailpit, puis définis un mot de passe robuste.</AppText>
        </View>

        <AppCard style={styles.form}>
          <ErrorMessage message={error} />
          {successMessage ? <AppText style={styles.success}>{successMessage}</AppText> : null}

          <AppInput
            autoCapitalize="none"
            label="Token"
            onChangeText={setToken}
            placeholder="Token reçu par email"
            value={token}
          />
          <AppInput
            label="Nouveau mot de passe"
            onChangeText={setNewPassword}
            placeholder="Votre nouveau mot de passe"
            secureTextEntry
            value={newPassword}
          />
          <PasswordRulesChecklist password={newPassword} />
          <AppInput
            label="Confirmer le nouveau mot de passe"
            onChangeText={setNewPasswordConfirmation}
            placeholder="Répéter le nouveau mot de passe"
            secureTextEntry
            value={newPasswordConfirmation}
          />

          <AppButton label="Réinitialiser" loading={isSubmitting} onPress={submitReset} />
          <AppButton label="Retour connexion" onPress={() => navigation.navigate('Login')} variant="secondary" />
        </AppCard>
      </KeyboardAvoidingView>
    </Screen>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    gap: theme.spacing.xl,
    justifyContent: 'center',
  },
  header: {
    gap: theme.spacing.sm,
  },
  kicker: {
    color: theme.colors.accent,
    fontSize: theme.typography.sizes.sm,
    fontWeight: theme.typography.weights.bold,
    textTransform: 'uppercase',
  },
  form: {
    gap: theme.spacing.lg,
  },
  success: {
    color: theme.colors.success,
    fontSize: theme.typography.sizes.sm,
    fontWeight: theme.typography.weights.semibold,
    lineHeight: 20,
  },
});
