import { NativeStackScreenProps } from '@react-navigation/native-stack';
import { useState } from 'react';
import { KeyboardAvoidingView, Platform, StyleSheet, View } from 'react-native';

import { PasswordRulesChecklist } from '../../components/auth/PasswordRulesChecklist';
import { AppButton } from '../../components/ui/AppButton';
import { AppInput } from '../../components/ui/AppInput';
import { AppText } from '../../components/ui/AppText';
import { ErrorMessage } from '../../components/ui/ErrorMessage';
import { Screen } from '../../components/ui/Screen';
import { theme } from '../../config/theme';
import { useAuth } from '../../hooks/useAuth';
import { AuthStackParamList } from '../../navigation/navigation.types';
import { toApiError } from '../../services/api/apiError';
import { isPasswordValid } from '../../utils/passwordValidation';

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

    if (!isPasswordValid(password)) {
      setError('Le mot de passe ne respecte pas encore toutes les règles.');
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
          <AppText variant="subtitle">Crée ton identité LeKlub et protège ton compte avec un mot de passe robuste.</AppText>
        </View>

        <View style={styles.form}>
          <ErrorMessage message={error} />
          {success ? <AppText style={styles.success}>{success}</AppText> : null}
          <AppInput
            autoComplete="email"
            keyboardType="email-address"
            label="Email"
            onChangeText={setEmail}
            placeholder="votre.email@exemple.com"
            value={email}
          />
          <AppInput
            autoCapitalize="none"
            label="Nom utilisateur"
            onChangeText={setUsername}
            placeholder="pseudo_leklub"
            value={username}
          />
          <AppInput
            label="Mot de passe"
            onChangeText={setPassword}
            placeholder="Votre mot de passe"
            secureTextEntry
            value={password}
          />
          <PasswordRulesChecklist password={password} />
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
