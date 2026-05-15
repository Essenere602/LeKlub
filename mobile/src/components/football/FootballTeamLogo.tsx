import { useState } from 'react';
import { Image, StyleSheet, View } from 'react-native';

import { theme } from '../../config/theme';
import { AppText } from '../ui/AppText';

type FootballTeamLogoProps = {
  fallback: string;
  size?: number;
  uri: string | null;
};

export function FootballTeamLogo({ fallback, size = 32, uri }: FootballTeamLogoProps) {
  const [hasImageError, setHasImageError] = useState(false);
  const showImage = Boolean(uri) && !hasImageError;

  return (
    <View style={[styles.container, { borderRadius: size / 2, height: size, width: size }]}>
      {showImage ? (
        <Image
          resizeMode="contain"
          source={{ uri: uri as string }}
          style={[styles.image, { height: size - 8, width: size - 8 }]}
          onError={() => setHasImageError(true)}
        />
      ) : (
        <AppText
          adjustsFontSizeToFit
          minimumFontScale={0.7}
          numberOfLines={1}
          style={[styles.fallback, { fontSize: Math.max(10, size * 0.32), lineHeight: Math.max(12, size * 0.4) }]}
        >
          {normalizeFallback(fallback)}
        </AppText>
      )}
    </View>
  );
}

function normalizeFallback(value: string): string {
  const normalizedValue = value
    .trim()
    .split(/\s+/)
    .map((word) => word.slice(0, 1))
    .join('')
    .slice(0, 3)
    .toUpperCase();

  return normalizedValue || 'FC';
}

const styles = StyleSheet.create({
  container: {
    alignItems: 'center',
    backgroundColor: theme.colors.football.surface,
    borderColor: theme.colors.football.border,
    borderWidth: 1,
    justifyContent: 'center',
    overflow: 'hidden',
  },
  image: {
    backgroundColor: 'transparent',
  },
  fallback: {
    color: theme.colors.football.neon,
    fontWeight: theme.typography.weights.bold,
    includeFontPadding: false,
    textAlign: 'center',
  },
});
