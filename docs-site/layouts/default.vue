<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  lang?: string
  doc?: Record<string, any>
}>()

const route = useRoute()
const currentLang = computed(() => props.lang ?? (Array.isArray(route.params.lang) ? route.params.lang[0] : (route.params.lang as string) ?? 'en'))
const isRtl = computed(() => currentLang.value === 'fa')

const navigation = computed(() => {
  const enNav = [
    {
      label: 'Getting started',
      links: [
        { title: 'Overview', path: '/en/index' },
        { title: 'Installation', path: '/en/installation' },
        { title: 'Quickstart', path: '/en/quickstart' },
        { title: 'Configuration', path: '/en/configuration' }
      ]
    },
    {
      label: 'Guides',
      links: [
        { title: 'Usage', path: '/en/usage' },
        { title: 'Advanced topics', path: '/en/advanced' },
        { title: 'Module anatomy', path: '/en/module-anatomy' },
        { title: 'Goli helper', path: '/en/goli-guide' },
        { title: 'GitHub Pages setup', path: '/en/github-pages-setup' }
      ]
    },
    {
      label: 'Reference',
      links: [
        { title: 'CLI Reference', path: '/en/reference' },
        { title: 'Changelog', path: '/en/changelog' }
      ]
    }
  ]

  const faNav = [
    {
      label: 'شروع',
      links: [
        { title: 'نمای کلی', path: '/fa/index' },
        { title: 'نصب', path: '/fa/installation' },
        { title: 'شروع سریع', path: '/fa/quickstart' },
        { title: 'پیکربندی', path: '/fa/configuration' }
      ]
    },
    {
      label: 'راهنما',
      links: [
        { title: 'نحوهٔ استفاده', path: '/fa/usage' },
        { title: 'پیشرفته', path: '/fa/advanced' },
        { title: 'آناتومی ماژول', path: '/fa/module-anatomy' },
        { title: 'راهنمای گُلی', path: '/fa/goli-guide' },
        { title: 'دیپلوی روی گیت‌هاب پیجز', path: '/fa/github-pages-setup' }
      ]
    },
    {
      label: 'مرجع',
      links: [
        { title: 'مرجع CLI', path: '/fa/reference' },
        { title: 'تغییرات', path: '/fa/changelog' }
      ]
    }
  ]

  return currentLang.value === 'fa' ? faNav : enNav
})

useHead({
  title: props.doc?.title ?? 'Laravel Module Generator',
  htmlAttrs: {
    lang: currentLang.value,
    dir: isRtl.value ? 'rtl' : 'ltr'
  }
})
</script>

<template>
  <div :class="[{ 'font-sans': true, 'antialiased': true, 'bg-slate-50': true, rtl: isRtl }]">
    <header class="border-b border-slate-200 bg-white/80 backdrop-blur">
      <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
        <div class="flex items-center gap-3">
          <NuxtLink to="/en" class="text-lg font-semibold text-primary-600">Laravel Module Generator</NuxtLink>
          <span class="hidden text-sm text-slate-500 sm:inline">Docs</span>
        </div>
        <nav class="flex items-center gap-4 text-sm font-medium text-slate-600">
          <NuxtLink to="/en/index" class="hover:text-primary-600" :class="{ 'text-primary-600': currentLang === 'en' }">English</NuxtLink>
          <span class="text-slate-300">·</span>
          <NuxtLink to="/fa/index" class="hover:text-primary-600" :class="{ 'text-primary-600': currentLang === 'fa' }">فارسی</NuxtLink>
          <a href="https://github.com/AfshinEfati/laravel-module-generator" target="_blank" rel="noopener" class="inline-flex items-center gap-1 rounded-md border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-600 hover:border-primary-500 hover:text-primary-600">
            <span>GitHub</span>
          </a>
        </nav>
      </div>
    </header>

    <div class="mx-auto flex max-w-6xl flex-col gap-10 px-4 py-10 lg:flex-row lg:px-6">
      <aside class="lg:w-64">
        <div class="sticky top-24 space-y-8">
          <template v-for="section in navigation" :key="section.label">
            <div>
              <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-500" :class="{ 'text-right': isRtl }">{{ section.label }}</p>
              <ul class="space-y-2" :class="{ 'text-right': isRtl }">
                <li v-for="link in section.links" :key="link.path">
                  <NuxtLink
                    :to="link.path"
                    class="block rounded-md px-3 py-2 text-sm transition hover:bg-primary-50 hover:text-primary-600"
                    :class="{
                      'bg-primary-100 text-primary-700 font-semibold': $route.path === link.path || $route.path === link.path.replace(/\/index$/, '')
                    }"
                  >
                    {{ link.title }}
                  </NuxtLink>
                </li>
              </ul>
            </div>
          </template>
        </div>
      </aside>

      <main class="min-w-0 flex-1">
        <article class="prose prose-slate max-w-none" :class="{ 'prose-lg text-right' : isRtl, 'prose-code:font-mono': true }">
          <slot />
        </article>
      </main>
    </div>
  </div>
</template>

<style scoped>
.rtl {
  direction: rtl;
}
</style>
