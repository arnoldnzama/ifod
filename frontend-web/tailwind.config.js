/** @type {import('tailwindcss').Config} */
export default {
  // Disable Tailwind preflight to avoid clashing with MUI's CssBaseline.
  corePlugins: {
    preflight: false,
  },
  content: ['./index.html', './src/**/*.{js,ts,jsx,tsx}'],
  theme: {
    extend: {
      colors: {
        brand: {
          50: '#eef4ff',
          100: '#d9e6ff',
          500: '#1e5eff',
          600: '#1647d6',
          700: '#1238a8',
        },
      },
    },
  },
  plugins: [],
}
