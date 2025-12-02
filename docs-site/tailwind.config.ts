import type { Config } from "tailwindcss";
import typography from "@tailwindcss/typography";

export default {
  content: [
    "./components/**/*.{vue,js,ts}",
    "./layouts/**/*.vue",
    "./pages/**/*.vue",
    "./app.vue",
    "./content/**/*.{md,yml,json}",
  ],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        primary: {
          50: "#FEF2F2",
          100: "#FEE2E2",
          200: "#FECACA",
          300: "#FCA5A5",
          400: "#F87171",
          500: "#EF4444",
         600: "#FF2D20", // Laravel red
          700: "#DC2626",
          800: "#B91C1C",
          900: "#991B1B",
          950: "#7F1D1D",
        },
        secondary: {
          50: "#F8FAFC",
          100: "#F1F5F9",
          200: "#E2E8F0",
          300: "#CBD5E1",
          400: "#94A3B8",
          500: "#64748B",
          600: "#475569",
          700: "#334155",
          800: "#1E293B",
          900: "#0F172A",
        },
      },
      fontFamily: {
        sans: [
          "Inter",
          "ui-sans-serif",
          "system-ui",
          "-apple-system",
          "BlinkMacSystemFont",
          "Segoe UI",
          "Roboto",
          "sans-serif",
        ],
        mono: ['"Fira Code"', "ui-monospace", "SFMono-Regular", "Monaco"],
      },
      typography: ({ theme }: any) => ({
        DEFAULT: {
          css: {
            maxWidth: "72ch",
            color: theme("colors.slate.700"),
            a: {
              color: theme("colors.primary.600"),
              fontWeight: "600",
              textDecoration: "none",
              transition: "color 0.2s ease",
              "&:hover": {
                color: theme("colors.primary.700"),
                textDecoration: "underline",
              },
            },
            code: {
              fontFamily: theme("fontFamily.mono").join(", "),
              borderRadius: theme("borderRadius.md"),
              backgroundColor: theme("colors.slate.100"),
              color: theme("colors.primary.600"),
              padding: "0.2rem 0.4rem",
              fontWeight: "500",
              fontSize: "0.875em",
            },
            "pre code": {
              backgroundColor: "transparent",
              padding: "0",
              color: "inherit",
              fontWeight: "normal",
            },
            pre: {
              fontFamily: theme("fontFamily.mono").join(", "),
              backgroundColor: "#282c34",
              color: "#abb2bf",
              padding: theme("spacing.4"),
              borderRadius: theme("borderRadius.lg"),
              border: "none",
              boxShadow: "0 4px 6px -1px rgba(0, 0, 0, 0.1)",
            },
            h1: {
              color: theme("colors.slate.900"),
              fontWeight: "800",
            },
            h2: {
              color: theme("colors.slate.900"),
              fontWeight: "700",
            },
            h3: {
              color: theme("colors.slate.800"),
              fontWeight: "600",
            },
            strong: {
              color: theme("colors.slate.900"),
              fontWeight: "600",
            },
          },
        },
        dark: {
          css: {
            color: theme("colors.slate.300"),
            a: {
              color: theme("colors.primary.400"),
              "&:hover": {
                color: theme("colors.primary.300"),
              },
            },
            code: {
              backgroundColor: theme("colors.slate.800"),
              color: theme("colors.primary.400"),
            },
            pre: {
              backgroundColor: "#1e1e1e",
              color: "#d4d4d4",
              boxShadow: "0 4px 6px -1px rgba(0, 0, 0, 0.5)",
            },
            h1: {
              color: theme("colors.slate.100"),
            },
            h2: {
              color: theme("colors.slate.100"),
            },
            h3: {
              color: theme("colors.slate.200"),
            },
            strong: {
              color: theme("colors.slate.100"),
            },
            blockquote: {
              color: theme("colors.slate.400"),
              borderLeftColor: theme("colors.primary.500"),
            },
          },
        },
      }),
    },
  },
  plugins: [typography],
} satisfies Config;
