import { defineNuxtConfig } from "nuxt/config";
import { joinURL } from "ufo";

const siteBaseURL =
  process.env.NUXT_PUBLIC_BASE_URL ??
  (process.env.NODE_ENV === "production" ? "/Laravel-Scaffolder/" : "/");
const faviconHref =
  siteBaseURL === "/" ? "/favicon.svg" : `${siteBaseURL}favicon.svg`;
const rootRedirect = joinURL(siteBaseURL, "en");

export default defineNuxtConfig({
  ssr: true,
  nitro: {
    prerender: {
      crawlLinks: true,
      routes: [
        "/en",
        "/fa",
        // English pages
        "/en/installation",
        "/en/quickstart",
        "/en/configuration",
        "/en/reference",
        "/en/changelog",
        "/en/github-pages-setup",
        "/en/api-reference",
        "/en/usage-examples",
        "/en/complete-features-guide",
        "/en/route-based-swagger",
        // Features
        "/en/features/generating-modules",
        "/en/features/schema-aware-generation",
        "/en/features/action-layer",
        "/en/features/swagger-generation",
        "/en/features/jalali-support",
        "/en/features/web-ui",
        "/en/features/policy-generation",
        "/en/features/criteria-pattern",
        "/en/features/dto-generation",
        "/en/features/test-generation",
        // Persian pages
        "/fa/installation",
        "/fa/quickstart",
        "/fa/configuration",
        "/fa/reference",
        "/fa/changelog",
        "/fa/github-pages-setup",
        "/fa/api-reference",
        "/fa/usage-examples",
        "/fa/complete-features-guide",
        "/fa/route-based-swagger",
        // Persian Features
        "/fa/features/generating-modules",
        "/fa/features/schema-aware-generation",
        "/fa/features/action-layer",
        "/fa/features/swagger-generation",
        "/fa/features/jalali-support",
        "/fa/features/web-ui",
        "/fa/features/policy-generation",
        "/fa/features/criteria-pattern",
        "/fa/features/dto-generation",
        "/fa/features/test-generation",
      ],
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
        default: "one-dark-pro",
        dark: "one-dark-pro",
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
      titleTemplate: "%s · Laravel Scaffolder",
      link: [{ rel: "icon", type: "image/svg+xml", href: faviconHref }],
      meta: [
        { charset: "utf-8" },
        { name: "viewport", content: "width=device-width, initial-scale=1" },
        {
          name: "description",
          content:
            "A powerful Laravel package for scaffolding complete feature stacks with Repository, Service, DTO, Policy, Tests, and more using clean architecture patterns.",
        },
        {
          name: "keywords",
          content:
            "Laravel, Scaffolder, Scaffold, PHP, Repository Pattern, Service Layer, Clean Architecture, Laravel Package",
        },

        // Open Graph
        { property: "og:type", content: "website" },
        { property: "og:site_name", content: "Laravel Scaffolder" },
        {
          property: "og:title",
          content:
            "Laravel Scaffolder - Build Complete Feature Stacks",
        },
        {
          property: "og:description",
          content:
            "A powerful Laravel package for scaffolding complete feature stacks with Repository, Service, DTO, Policy, Tests, and more using clean architecture patterns.",
        },
        {
          property: "og:url",
          content: "https://afshinefati.github.io/Laravel-Scaffolder/",
        },
        {
          property: "og:image",
          content:
            "https://afshinefati.github.io/Laravel-Scaffolder/og-image.png",
        },
        { property: "og:image:width", content: "1200" },
        { property: "og:image:height", content: "630" },

        // Twitter Card
        { name: "twitter:card", content: "summary_large_image" },
        {
          name: "twitter:title",
          content:
            "Laravel Scaffolder - Build Complete Feature Stacks",
        },
        {
          name: "twitter:description",
          content:
            "A powerful Laravel package for scaffolding complete feature stacks with Repository, Service, DTO, Policy, Tests, and more using clean architecture patterns.",
        },
        {
          name: "twitter:image",
          content:
            "https://afshinefati.github.io/Laravel-Scaffolder/og-image.png",
        },

        // Additional
        { name: "author", content: "Afshin Efati" },
        { name: "theme-color", content: "#3b82f6" },
      ],
    },
  },
  runtimeConfig: {
    public: {
      siteName: "Laravel Scaffolder",
      basePath: siteBaseURL,
    },
  },
  devtools: { enabled: false },
});
