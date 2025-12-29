/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./**/*.php", "./**/*.html", "./assets/js/**/*.js"],
  theme: {
    extend: {
      colors: {
        // MDM Design System Colors
        mdm: {
          sidebar: "#000000", // Pure black
          black: "#000000",
          bg: "#E6E7E2", // Main beige background
          card: "#FFFFFF",
          pill: "#E8E6E1", // Floating pill background
          "maintenance-bg": "#F2F2F0", // Light gray for maintenance cards
          accent: "#D9D9D6",
          text: "#000000",
          positive: "#4CAF50", // +2% green
          success: "#4CAF50",
          warning: "#FFB300",
          alert: "#E53935",
        },
      },
      fontFamily: {
        sans: ["Poppins", "Inter", "system-ui", "sans-serif"],
      },
      fontSize: {
        stat: ["3.5rem", { lineHeight: "1.1", fontWeight: "700" }],
        "stat-lg": ["4rem", { lineHeight: "1.1", fontWeight: "700" }],
        heading: ["2rem", { lineHeight: "1.2", fontWeight: "700" }],
        label: ["1rem", { lineHeight: "1.5", fontWeight: "500" }],
        subtext: ["0.875rem", { lineHeight: "1.5", fontWeight: "400" }],
      },
      borderRadius: {
        card: "20px",
        pill: "9999px",
      },
      spacing: {
        sidebar: "260px",
        "card-gap": "24px",
        "section-gap": "32px",
      },
      boxShadow: {
        card: "0 4px 20px rgba(0, 0, 0, 0.06)",
        "card-hover": "0 8px 30px rgba(0, 0, 0, 0.1)",
      },
    },
  },
  plugins: [],
};
