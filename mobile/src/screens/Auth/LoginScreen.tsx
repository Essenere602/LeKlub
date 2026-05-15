import { NativeStackScreenProps } from '@react-navigation/native-stack';
import { useState } from 'react';
import { KeyboardAvoidingView, Platform, StyleSheet, View } from 'react-native';

import { AppButton } from '../../components/ui/AppButton';
import { AppInput } from '../../components/ui/AppInput';
import { AppText } from '../../components/ui/AppText';
import { ErrorMessage } from '../../components/ui/ErrorMessage';
import { Screen } from '../../components/ui/Screen';
import { theme } from '../../config/theme';
import { AuthStackParamList } from '../../navigation/navigation.types';
import { toApiError } from '../../services/api/apiError';
import { useAuth } from '../../hooks/useAuth';

type LoginScreenProps = NativeStackScreenProps<AuthStackParamList, 'Login'>;

export function LoginScreen({ navigation }: LoginScreenProps) {
  const { login } = useAuth();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);

  async function handleLogin() {
    setError(null);

    if (!email.trim() || !password) {
      setError('Email et mot de passe sont requis.');
      return;
    }

    try {
      setIsSubmitting(true);
      await login({ email: email.trim(), password });
    } catch (loginError) {
      setError(toApiError(loginError).message);
    } finally {
      setIsSubmitting(false);
    }
  }

  return (
    <Screen>
      <KeyboardAvoidingView behavior={Platform.OS === 'ios' ? 'padding' : undefined} style={styles.container}>
        <View style={styles.header}>
          <AppText style={styles.kicker}>LeKlub</AppText>
          <AppText variant="title">Connexion</AppText>
          <AppText variant="subtitle">Retrouvez votre feed, vos conversations et les données football.</AppText>
        </View>

        <View style={styles.form}>
          <ErrorMessage message={error} />
          <AppInput
            autoComplete="email"
            keyboardType="email-address"
            label="Email"
            onChangeText={setEmail}
            placeholder="user@example.com"
            value={email}
          />
          <AppInput
            label="Mot de passe"
            onChangeText={setPassword}
            placeholder="Password123!"
            secureTextEntry
            value={password}
          />
          <AppButton label="Se connecter" loading={isSubmitting} onPress={handleLogin} />
          <AppButton label="Créer un compte" onPress={() => navigation.navigate('Register')} variant="ghost" />
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
});
