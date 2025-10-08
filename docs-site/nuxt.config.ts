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
    '/en/index': { redirect: '/en' },
    '/fa/index': { redirect: '/fa' }
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
      crawlLinks: true,
      routes: ['/', '/en/index', '/fa/index'] // changed prerender routes
    }
  },
  devtools: { enabled: false }
})
