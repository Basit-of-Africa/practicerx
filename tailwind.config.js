module.exports = {
  content: ['./src/**/*.{js,jsx,ts,tsx}', './build/**/*.js'],
  theme: {
    extend: {
      colors: {
        primary: 'var(--color-primary)',
        accent: 'var(--color-accent)',
        surface: 'var(--color-surface)',
        muted: 'var(--color-muted)',
      },
      spacing: {
        'safe': 'var(--space-safe)',
      }
    },
  },
  plugins: [],
};
