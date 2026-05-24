import { NativeStackScreenProps } from '@react-navigation/native-stack';
import { useState } from 'react';
import { KeyboardAvoidingView, Platform, StyleSheet, View } from 'react-native';

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

type ForgotPasswordScreenProps = NativeStackScreenProps<AuthStackParamList, 'ForgotPassword'>;

export function ForgotPasswordScreen({ navigation }: ForgotPasswordScreenProps) {
  const [email, setEmail] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [successMessage, setSuccessMessage] = useState<string | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);

  async function submitRequest() {
    setError(null);
    setSuccessMessage(null);

    if (!email.trim()) {
      setError("L'email est requis.");
      return;
    }

    try {
      setIsSubmitting(true);
      await authService.forgotPassword({ email: email.trim() });
      setSuccessMessage('Si cet email existe, les instructions de réinitialisation ont été envoyées.');
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
          <AppText style={styles.kicker}>Sécurité</AppText>
          <AppText variant="title">Mot de passe oublié</AppText>
          <AppText variant="subtitle">
            Saisis ton email. En développement, le token est disponible dans Mailpit.
          </AppText>
        </View>

        <AppCard style={styles.form}>
          <ErrorMessage message={error} />
          {successMessage ? <AppText style={styles.success}>{successMessage}</AppText> : null}

          <AppInput
            autoComplete="email"
            autoCapitalize="none"
            keyboardType="email-address"
            label="Email"
            onChangeText={setEmail}
            placeholder="votre.email@exemple.com"
            value={email}
          />

          <AppButton label="Demander un reset" loading={isSubmitting} onPress={submitRequest} />
          <AppButton
            label="J'ai un token"
            onPress={() => navigation.navigate('ResetPassword', { email: email.trim() || undefined })}
            variant="secondary"
          />
          <AppButton label="Retour connexion" onPress={() => navigation.navigate('Login')} variant="ghost" />
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
