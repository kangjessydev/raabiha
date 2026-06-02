/** @type {import('tailwindcss').Config} */
export default {
  // Scan Vue SFCs, JS files, WordPress PHP files, and block JSONs for Tailwind classes
  content: [
    './src/**/*.{vue,js,ts,jsx,tsx}',
    './*.php',
    './inc/**/*.php',
    './templates/**/*.php',
    './woocommerce/**/*.php',
    '../*.json',
  ],

  theme: {
    extend: {
      // Raabiha design tokens
      colors: {
        raabiha: {
          primary:   '#222523',
          secondary: '#615E57',
          emerald:   '#0B4E26',
          ivory:     '#FAF7F0',
          tertiary:  '#2F0D05',
          surface: {
            DEFAULT: '#FAF7F0',
            2:       '#F2EFE8',
            3:       '#E5E1D8',
          },
        },
      },
      fontFamily: {
        sans: ['"Poppins"', 'sans-serif'],
        serif: ['"Playfair Display"', 'Georgia', 'serif'],
        mono: ['"JetBrains Mono"', 'monospace'],
        playfair: ['"Playfair Display"', 'Georgia', 'serif'],
        inter: ['"Inter"', 'system-ui', 'sans-serif'],
      },
      backgroundImage: {
        'gradient-raabiha': 'linear-gradient(135deg, #222523 0%, #0B4E26 100%)',
      },
      animation: {
        'fade-in':    'fadeIn 0.3s ease-in-out',
        'fade-in-up': 'fadeInUp 0.8s ease-out forwards',
        'slide-up':   'slideUp 0.3s ease-out',
        'pulse-soft': 'pulseSoft 2s ease-in-out infinite',
      },
      keyframes: {
        fadeIn: {
          '0%':   { opacity: '0' },
          '100%': { opacity: '1' },
        },
        fadeInUp: {
          '0%':   { opacity: '0', transform: 'translateY(20px)' },
          '100%': { opacity: '1', transform: 'translateY(0)' },
        },
        slideUp: {
          '0%':   { opacity: '0', transform: 'translateY(12px)' },
          '100%': { opacity: '1', transform: 'translateY(0)' },
        },
        pulseSoft: {
          '0%, 100%': { opacity: '1' },
          '50%':      { opacity: '0.6' },
        },
      },
      boxShadow: {
        'card':   '0 4px 24px rgba(34, 37, 35, 0.05)',
      },
    },
  },

  plugins: [],
}
