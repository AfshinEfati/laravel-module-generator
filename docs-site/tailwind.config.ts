import type { Config } from 'tailwindcss'
import typography from '@tailwindcss/typography'

export default <Partial<Config>>{
  content: [
    './components/**/*.{vue,js,ts}',
    './layouts/**/*.vue',
    './pages/**/*.vue',
    './app.vue',
    './content/**/*.{md,yml,json}'
  ],
  darkMode: ['class', '[data-theme="dark"]'],
  theme: {
    extend: {
      colors: {
        primary: {
          50: '#eff6ff',
          100: '#dbeafe',
          200: '#bfdbfe',
          300: '#93c5fd',
          400: '#60a5fa',
          500: '#3b82f6',
          600: '#2563eb',
          700: '#1d4ed8',
          800: '#1e40af',
          900: '#1e3a8a'
        }
      },
      fontFamily: {
        sans: ['ui-sans-serif', 'system-ui', 'sans-serif'],
        mono: ['"Fira Code"', 'ui-monospace', 'SFMono-Regular']
      },
      typography: ({ theme }) => ({
        DEFAULT: {
          css: {
            maxWidth: '72ch',
            color: theme('colors.gray.800'),
            a: {
              color: theme('colors.primary.600'),
              fontWeight: '500',
              textDecoration: 'none',
              '&:hover': {
                textDecoration: 'underline'
              }
            },
            code: {
              fontFamily: theme('fontFamily.mono').join(', '),
              borderRadius: theme('borderRadius.md'),
              backgroundColor: theme('colors.gray.100'),
              color: theme('colors.gray.900'),
              padding: '0.125rem 0.375rem'
            },
            'pre code': {
              backgroundColor: 'transparent',
              padding: '0'
            },
            pre: {
              fontFamily: theme('fontFamily.mono').join(', '),
              backgroundColor: '#282a36',
              color: '#f8f8f2',
              padding: theme('spacing.4'),
              borderRadius: theme('borderRadius.lg'),
              borderWidth: '1px',
              borderColor: '#44475a'
            }
          }
        }
      })
    }
  },
  plugins: [typography]
}
