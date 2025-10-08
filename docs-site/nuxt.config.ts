import { defineNuxtConfig } from 'nuxt/config'

export default defineNuxtConfig({
  modules: ['@nuxt/content', '@nuxtjs/tailwindcss'],
  css: ['~/assets/css/main.css'],
  content: {
    highlight: {
      theme: {
        default: 'github-light',
        dark: 'github-dark'
      },
      preload: ['php', 'bash', 'json', 'yaml', 'diff']
    }
  },
  routeRules: {
    '/': { redirect: '/en' }
  },
  app: {
    head: {
      titleTemplate: '%s · Laravel Module Generator',
      link: [
        { rel: 'icon', type: 'image/svg+xml', href: '/favicon.svg' }
      ]
    }
  },
  tailwindcss: {
    viewer: false
  },
  runtimeConfig: {
    public: {
      siteName: 'Laravel Module Generator'
    }
  },
  nitro: {
    prerender: {
      crawlLinks: false,
      routes: [
        '/',
        '/en',
        '/fa',
        '/en/installation',
        '/en/quickstart',
        '/en/configuration',
        '/en/usage',
        '/en/advanced',
        '/en/module-anatomy',
        '/en/goli-guide',
        '/en/github-pages-setup',
        '/en/reference',
        '/en/changelog',
        '/fa/installation',
        '/fa/quickstart',
        '/fa/configuration',
        '/fa/usage',
        '/fa/advanced',
        '/fa/module-anatomy',
        '/fa/goli-guide',
        '/fa/github-pages-setup',
        '/fa/reference',
        '/fa/changelog'
      ]
    }
  },
  devtools: { enabled: false }
})
