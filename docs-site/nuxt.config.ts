import { defineNuxtConfig } from "nuxt/config";
import { joinURL } from "ufo";

const siteBaseURL =
  process.env.NUXT_PUBLIC_BASE_URL ??
  (process.env.NODE_ENV === "production" ? "/laravel-module-generator/" : "/");
const faviconHref =
  siteBaseURL === "/" ? "/favicon.svg" : `${siteBaseURL}favicon.svg`;
const rootRedirect = joinURL(siteBaseURL, "en");

export default defineNuxtConfig({
  ssr: true,
  nitro: {
    prerender: {
      crawlLinks: true,
      routes: ["/en", "/fa"],
      ignore: ["/sitemap.xml", "/admin"],
    },
  },
  modules: ["@nuxt/content", "@nuxtjs/tailwindcss"],
  css: ["~/assets/css/main.css"],
  build: {
    transpile: ["tailwindcss"],
  },
  tailwindcss: {
    exposeConfig: true,
    viewer: false,
    editorSupport: true,
  },
  content: {
    documentDriven: false,
    highlight: {
      theme: {
        default: "min-light",
        dark: "min-light",
      },
      preload: ["php", "bash", "json", "yaml", "diff"],
    },
    markdown: {
      toc: {
        depth: 3,
        searchDepth: 3,
      },
    },
  },
  routeRules: {
    "/": { redirect: rootRedirect },
  },
  app: {
    baseURL: siteBaseURL,
    head: {
      titleTemplate: "%s · Laravel Module Generator",
      link: [{ rel: "icon", type: "image/svg+xml", href: faviconHref }],
      meta: [
        { charset: "utf-8" },
        { name: "viewport", content: "width=device-width, initial-scale=1" },
        {
          name: "description",
          content:
            "A powerful Laravel package for generating modular application structures with clean architecture and best practices.",
        },
        {
          name: "keywords",
          content:
            "Laravel, Module Generator, PHP, Modular Architecture, Laravel Package",
        },

        // Open Graph
        { property: "og:type", content: "website" },
        { property: "og:site_name", content: "Laravel Module Generator" },
        {
          property: "og:title",
          content:
            "Laravel Module Generator - Build Modular Laravel Applications",
        },
        {
          property: "og:description",
          content:
            "A powerful Laravel package for generating modular application structures with clean architecture and best practices.",
        },
        {
          property: "og:url",
          content: "https://afshinefati.github.io/laravel-module-generator/",
        },
        {
          property: "og:image",
          content:
            "https://afshinefati.github.io/laravel-module-generator/og-image.png",
        },
        { property: "og:image:width", content: "1200" },
        { property: "og:image:height", content: "630" },

        // Twitter Card
        { name: "twitter:card", content: "summary_large_image" },
        {
          name: "twitter:title",
          content:
            "Laravel Module Generator - Build Modular Laravel Applications",
        },
        {
          name: "twitter:description",
          content:
            "A powerful Laravel package for generating modular application structures with clean architecture and best practices.",
        },
        {
          name: "twitter:image",
          content:
            "https://afshinefati.github.io/laravel-module-generator/og-image.png",
        },

        // Additional
        { name: "author", content: "Afshin Efati" },
        { name: "theme-color", content: "#3b82f6" },
      ],
    },
  },
  runtimeConfig: {
    public: {
      siteName: "Laravel Module Generator",
      basePath: siteBaseURL,
    },
  },
  devtools: { enabled: false },
});
