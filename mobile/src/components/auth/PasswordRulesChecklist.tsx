import { Ionicons } from '@expo/vector-icons';
import { StyleSheet, View } from 'react-native';

import { theme } from '../../config/theme';
import { passwordRules } from '../../utils/passwordValidation';
import { AppText } from '../ui/AppText';

type PasswordRulesChecklistProps = {
  password: string;
};

export function PasswordRulesChecklist({ password }: PasswordRulesChecklistProps) {
  return (
    <View style={styles.container}>
      <AppText variant="label">Règles du mot de passe</AppText>
      <View style={styles.rules}>
        {passwordRules.map((rule) => {
          const isValid = rule.isValid(password);

          return (
            <View key={rule.id} style={styles.rule}>
              <Ionicons
                color={isValid ? theme.colors.success : theme.colors.text.muted}
                name={isValid ? 'checkmark-circle' : 'ellipse-outline'}
                size={16}
              />
              <AppText style={[styles.ruleText, isValid && styles.validRuleText]}>{rule.label}</AppText>
            </View>
          );
        })}
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    backgroundColor: theme.colors.surfaceElevated,
    borderColor: theme.colors.borderSoft,
    borderRadius: theme.radius.md,
    borderWidth: 1,
    gap: theme.spacing.sm,
    padding: theme.spacing.md,
  },
  rules: {
    gap: theme.spacing.xs,
  },
  rule: {
    alignItems: 'center',
    flexDirection: 'row',
    gap: theme.spacing.sm,
  },
  ruleText: {
    color: theme.colors.text.muted,
    flex: 1,
    fontSize: theme.typography.sizes.sm,
    lineHeight: 20,
  },
  validRuleText: {
    color: theme.colors.text.secondary,
  },
});
