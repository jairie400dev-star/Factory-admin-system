/** @type {import('tailwindcss').Config} */
export default {
  content: ['./index.html', './src/**/*.{vue,js,ts,jsx,tsx}'],
  theme: {
    extend: {
      colors: {
        // Warm cream canvas
        canvas: '#f5f1ec',
        surface: '#ffffff',
        // Dark sidebar / promo panel
        ink: {
          DEFAULT: '#1c1a17',
          900: '#161412',
          800: '#211e1a',
          700: '#2b2723',
        },
        // Terracotta accent
        primary: {
          DEFAULT: '#c87a5b',
          50: '#faf2ee',
          100: '#f3ddd3',
          200: '#e8bda8',
          300: '#dc9b7d',
          400: '#d18a68',
          500: '#c87a5b',
          600: '#b5654a',
          700: '#97503a',
        },
        line: '#e7e0d7',
      },
      fontFamily: {
        serif: ['Fraunces', 'Georgia', 'serif'],
        sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
        mono: ['"JetBrains Mono"', 'ui-monospace', 'SFMono-Regular', 'monospace'],
      },
      boxShadow: {
        card: '0 1px 2px rgba(28, 26, 23, 0.04), 0 1px 12px rgba(28, 26, 23, 0.05)',
      },
    },
  },
  plugins: [],
}
