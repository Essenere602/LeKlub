import { NativeStackScreenProps } from '@react-navigation/native-stack';
import { useState } from 'react';
import { KeyboardAvoidingView, Platform, StyleSheet, View } from 'react-native';

import { AppButton } from '../../components/ui/AppButton';
import { AppInput } from '../../components/ui/AppInput';
import { AppText } from '../../components/ui/AppText';
import { ErrorMessage } from '../../components/ui/ErrorMessage';
import { Screen } from '../../components/ui/Screen';
import { theme } from '../../config/theme';
import { useAuth } from '../../hooks/useAuth';
import { AuthStackParamList } from '../../navigation/navigation.types';
import { toApiError } from '../../services/api/apiError';

type RegisterScreenProps = NativeStackScreenProps<AuthStackParamList, 'Register'>;

export function RegisterScreen({ navigation }: RegisterScreenProps) {
  const { register } = useAuth();
  const [email, setEmail] = useState('');
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [success, setSuccess] = useState<string | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);

  async function handleRegister() {
    setError(null);
    setSuccess(null);

    if (!email.trim() || !username.trim() || !password) {
      setError('Email, nom utilisateur et mot de passe sont requis.');
      return;
    }

    if (!/^[a-zA-Z0-9_]{3,50}$/.test(username.trim())) {
      setError('Le nom utilisateur doit contenir 3 à 50 caractères : lettres, chiffres ou underscore uniquement.');
      return;
    }

    if (password.length < 10 || !/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/.test(password)) {
      setError('Le mot de passe doit contenir au moins 10 caractères, une minuscule, une majuscule et un chiffre.');
      return;
    }

    try {
      setIsSubmitting(true);
      await register({
        email: email.trim(),
        username: username.trim(),
        password,
      });
      setSuccess('Compte créé. Vous pouvez maintenant vous connecter.');
    } catch (registerError) {
      setError(toApiError(registerError).message);
    } finally {
      setIsSubmitting(false);
    }
  }

  return (
    <Screen>
      <KeyboardAvoidingView behavior={Platform.OS === 'ios' ? 'padding' : undefined} style={styles.container}>
        <View style={styles.header}>
          <AppText style={styles.kicker}>LeKlub</AppText>
          <AppText variant="title">Créer un compte</AppText>
          <AppText variant="subtitle">Un compte simple pour accéder au MVP mobile.</AppText>
        </View>

        <View style={styles.form}>
          <ErrorMessage message={error} />
          {success ? <AppText style={styles.success}>{success}</AppText> : null}
          <AppInput
            autoComplete="email"
            keyboardType="email-address"
            label="Email"
            onChangeText={setEmail}
            placeholder="user@example.com"
            value={email}
          />
          <AppInput
            autoCapitalize="none"
            label="Nom utilisateur"
            onChangeText={setUsername}
            placeholder="samuel"
            value={username}
          />
          <AppInput
            label="Mot de passe"
            onChangeText={setPassword}
            placeholder="Password123!"
            secureTextEntry
            value={password}
          />
          <AppText variant="muted">
            Minimum 10 caractères, avec une majuscule, une minuscule et un chiffre.
          </AppText>
          <AppButton label="Créer le compte" loading={isSubmitting} onPress={handleRegister} />
          <AppButton label="J'ai déjà un compte" onPress={() => navigation.navigate('Login')} variant="ghost" />
        </View>
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
  },
});
