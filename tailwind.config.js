/** @type {import('tailwindcss').Config} */
export default {
  corePlugins: {
    preflight: false,
  },
  theme: {
    extend: {
      fontFamily: {
        sans: ['Inter', 'sans-serif'],
      },
      colors: {
        primary: '#1e40af',
        'primary-dark': '#1e3a8a',
        'primary-light': '#3b82f6',
        navy: {
          900: '#1e3a8a',
          800: '#1e40af',
        },
      },
    },
  },
}
