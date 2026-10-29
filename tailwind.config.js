/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './app/Views/**/*.php',
    './app/Helpers/**/*.php',
    './app/Controllers/**/*.php',
    './public/assets/js/**/*.js',
  ],
  theme: {
    extend: {
      colors: {
        ink: '#101828',
        cream: '#F6F0E4',
        paper: '#FFFCF5',
        coral: '#F45B4F',
        sun: '#FFC93C',
        mint: '#72E6B7',
        cobalt: '#2F5BFF',
        lilac: '#C7B7FF',
        sky: '#BEE3FF',
        line: '#E3D9C6',
        muted: '#667085',
      },
      fontFamily: {
        sans: ['Outfit', 'system-ui', 'sans-serif'],
        display: ['"Space Grotesk"', 'system-ui', 'sans-serif'],
      },
      boxShadow: {
        hard: '6px 6px 0 0 #101828',
        'hard-sm': '4px 4px 0 0 #101828',
        'hard-coral': '6px 6px 0 0 #F45B4F',
        'hard-cobalt': '6px 6px 0 0 #2F5BFF',
        soft: '0 24px 60px -28px rgba(16, 24, 40, 0.35)',
      },
      borderRadius: {
        '4xl': '2rem',
        '5xl': '2.75rem',
      },
      maxWidth: {
        '8xl': '90rem',
      },
      keyframes: {
        float: {
          '0%, 100%': { transform: 'translateY(0)' },
          '50%': { transform: 'translateY(-10px)' },
        },
        fadeUp: {
          '0%': { opacity: '0', transform: 'translateY(16px)' },
          '100%': { opacity: '1', transform: 'translateY(0)' },
        },
        marquee: {
          '0%': { transform: 'translateX(0)' },
          '100%': { transform: 'translateX(-50%)' },
        },
      },
      animation: {
        float: 'float 6s ease-in-out infinite',
        'fade-up': 'fadeUp .6s ease-out both',
        marquee: 'marquee 28s linear infinite',
      },
    },
  },
  plugins: [],
};