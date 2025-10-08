import { defineNuxtConfig } from 'nuxt/config'
import { joinURL } from 'ufo'

const baseURL =
  process.env.NUXT_PUBLIC_BASE_URL ??
  (process.env.NODE_ENV === 'production' ? '/laravel-module-generator/' : '/')
const faviconHref = baseURL === '/' ? '/favicon.svg' : `${baseURL}favicon.svg`
const rootRedirect = joinURL(baseURL, 'en')

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
    '/': { redirect: rootRedirect }
  },
  app: {
    baseURL,
    head: {
      titleTemplate: '%s · Laravel Module Generator',
      link: [
        { rel: 'icon', type: 'image/svg+xml', href: faviconHref }
      ]
    }
  },
  tailwindcss: {
    viewer: false
  },
  runtimeConfig: {
    public: {
      siteName: 'Laravel Module Generator',
      basePath: baseURL
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
