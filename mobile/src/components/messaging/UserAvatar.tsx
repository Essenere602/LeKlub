import { useState } from 'react';
import { Image, StyleSheet, View } from 'react-native';

import { theme } from '../../config/theme';
import { AppText } from '../ui/AppText';

type UserAvatarProps = {
  label: string;
  uri?: string | null;
  size?: number;
};

export function UserAvatar({ label, size = 42, uri }: UserAvatarProps) {
  const [hasImageError, setHasImageError] = useState(false);
  const initials = getInitials(label);

  if (uri && !hasImageError) {
    return (
      <Image
        onError={() => setHasImageError(true)}
        source={{ uri }}
        style={[
          styles.avatar,
          {
            borderRadius: size / 2,
            height: size,
            width: size,
          },
        ]}
      />
    );
  }

  return (
    <View
      style={[
        styles.avatar,
        styles.fallback,
        {
          borderRadius: size / 2,
          height: size,
          width: size,
        },
      ]}
    >
      <AppText style={styles.initials}>{initials}</AppText>
    </View>
  );
}

function getInitials(label: string): string {
  return label
    .trim()
    .split(/\s+/)
    .slice(0, 2)
    .map((part) => part[0]?.toUpperCase())
    .join('') || '?';
}

const styles = StyleSheet.create({
  avatar: {
    backgroundColor: theme.colors.surfaceElevated,
    borderColor: theme.colors.border,
    borderWidth: 1,
  },
  fallback: {
    alignItems: 'center',
    justifyContent: 'center',
  },
  initials: {
    color: theme.colors.accent,
    fontSize: theme.typography.sizes.sm,
    fontWeight: theme.typography.weights.bold,
  },
});
