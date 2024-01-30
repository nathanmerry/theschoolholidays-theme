/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./**/*.php"],
  theme: {
    container: {
      center: true,
      padding: "1rem",
      screens: {
        sm: "600px",
        md: "728px",
        lg: "984px",
        xl: "1170px",
        "2xl": "1170px",
      },
    },
    extend: {
      fontFamily: {
        caveatBrush: ["'Caveat Brush'", "cursive"],
        oswald: ["'Oswald'", "sans-serif"],
      },
    },
  },
  plugins: [require("daisyui"), require("@tailwindcss/typography")],
  daisyui: {
    themes: ["emerald"],
  },
};
