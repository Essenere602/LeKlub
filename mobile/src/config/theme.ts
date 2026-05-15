export const theme = {
  colors: {
    background: '#080B0A',
    surface: '#121712',
    surfaceElevated: '#1A211A',
    border: '#263126',
    accent: '#C8FF00',
    accentSoft: 'rgba(200, 255, 0, 0.14)',
    success: '#39FF14',
    danger: '#FF3B5C',
    warning: '#FFCC00',
    text: {
      primary: '#F4F7F1',
      secondary: '#B7C0B2',
      muted: '#7C8877',
      inverse: '#080B0A',
    },
    football: {
      blackPure: '#000000',
      black: '#0A0A0A',
      surface: '#1A1A1A',
      card: '#252525',
      border: '#2A2A2A',
      muted: '#808080',
      neon: '#C8FF00',
      neonSoft: 'rgba(200, 255, 0, 0.12)',
      neonGlow: 'rgba(200, 255, 0, 0.32)',
      cyan: '#00FF88',
    },
  },
  spacing: {
    xs: 4,
    sm: 8,
    md: 12,
    lg: 16,
    xl: 24,
    '2xl': 32,
  },
  radius: {
    sm: 6,
    md: 8,
    lg: 12,
  },
  typography: {
    sizes: {
      xs: 12,
      sm: 14,
      md: 16,
      lg: 18,
      xl: 22,
      '2xl': 28,
    },
    weights: {
      regular: '400' as const,
      medium: '500' as const,
      semibold: '600' as const,
      bold: '700' as const,
    },
  },
};

export type Theme = typeof theme;
