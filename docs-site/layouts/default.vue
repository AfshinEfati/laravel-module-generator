<script setup lang="ts">
import { computed } from 'vue'
import { useHead, useRuntimeConfig, useState } from '#imports'
import { joinURL } from 'ufo'

const route = useRoute()
const runtimeConfig = useRuntimeConfig()
const hideNavigationState = useState<boolean>('hide-navigation', () => false)

const normalize = (path: string): string => {
  if (!path) {
    return '/'
  }
  const ensured = path.startsWith('/') ? path : `/${path}`
  return ensured.endsWith('/') ? ensured : `${ensured}/`
}

const currentLang = computed(() => {
  const lang = route.params.lang
  if (Array.isArray(lang)) {
    return lang[0] ?? 'en'
  }
  return (lang as string) ?? 'en'
})

const isRtl = computed(() => currentLang.value === 'fa')
const basePath = computed(() => {
  const configuredBase = runtimeConfig.public?.basePath as string | undefined
  if (!configuredBase) {
    return '/'
  }
  return normalize(configuredBase)
})

const basePathWithoutTrailingSlash = computed(() =>
  basePath.value.endsWith('/') && basePath.value !== '/' ? basePath.value.slice(0, -1) : basePath.value
)

const stripBaseFromPath = (path: string) => {
  const base = basePathWithoutTrailingSlash.value

  if (!base || base === '/' || !path.startsWith(base)) {
    return path
  }

  const stripped = path.slice(base.length)
  return stripped.startsWith('/') ? stripped : `/${stripped}`
}

const currentPath = computed(() => normalize(stripBaseFromPath(route.path)))
const isActiveLink = (path: string) => currentPath.value === normalize(path)

const withBasePath = (path: string) => joinURL(basePath.value, path)

const hideNavigation = computed(() => Boolean(hideNavigationState.value))

const contentContainerClasses = computed(() => [
  'mx-auto',
  'flex',
  'w-full',
  'gap-10',
  'px-4',
  'py-10',
  'lg:px-6',
  hideNavigation.value ? 'max-w-4xl flex-col' : 'max-w-6xl flex-col lg:flex-row'
])

const navigation = computed(() => {
  const enNav = [
    {
      label: 'Getting started',
      links: [
        { title: 'Overview', path: '/en' },
        { title: 'Installation', path: '/en/installation' },
        { title: 'Quickstart', path: '/en/quickstart' },
        { title: 'Configuration', path: '/en/configuration' }
      ]
    },
    {
      label: 'Features',
      links: [
        { title: 'Generating Modules', path: '/en/features/generating-modules' },
        { title: 'Schema-Aware Generation', path: '/en/features/schema-aware-generation' },
        { title: 'Action Layer', path: '/en/features/action-layer' },
        { title: 'Swagger Generation', path: '/en/features/swagger-generation' },
        { title: 'Policy Generation', path: '/en/features/policy-generation' },
        { title: 'Criteria Pattern', path: '/en/features/criteria-pattern' },
        { title: 'DTO Generation', path: '/en/features/dto-generation' },
        { title: 'Test Generation', path: '/en/features/test-generation' },
        { title: 'Jalali Support', path: '/en/features/jalali-support' },
        { title: 'Web UI', path: '/en/features/web-ui' },
      ],
    },
    {
      label: 'Reference',
      links: [
        { title: 'CLI Reference', path: '/en/reference' },
        { title: 'API Reference', path: '/en/api-reference' },
        { title: 'Changelog', path: '/en/changelog' }
      ]
    },
    {
      label: 'Learn',
      links: [
        { title: 'Usage Examples', path: '/en/usage-examples' },
        { title: 'Complete Features Guide', path: '/en/complete-features-guide' },
        { title: 'Route-Based Swagger', path: '/en/route-based-swagger' }
      ]
    }
  ]

  const faNav = [
    {
      label: 'شروع',
      links: [
        { title: 'نمای کلی', path: '/fa' },
        { title: 'نصب', path: '/fa/installation' },
        { title: 'شروع سریع', path: '/fa/quickstart' },
        { title: 'پیکربندی', path: '/fa/configuration' }
      ]
    },
    {
      label: 'امکانات',
      items: [
        { title: 'تولید ماژول‌ها', path: '/fa/features/generating-modules' },
        { title: 'تولید هوشمند از Schema', path: '/fa/features/schema-aware-generation' },
        { title: 'لایه Actions', path: '/fa/features/action-layer' },
        { title: 'تولید Swagger', path: '/fa/features/swagger-generation' },
        { title: 'تولید Policy', path: '/fa/features/policy-generation' },
        { title: 'الگوی Criteria', path: '/fa/features/criteria-pattern' },
        { title: 'تولید DTO', path: '/fa/features/dto-generation' },
        { title: 'تولید تست', path: '/fa/features/test-generation' },
        { title: 'پشتیبانی جلالی', path: '/fa/features/jalali-support' },
        { title: 'رابط کاربری وب', path: '/fa/features/web-ui' },
      ],
    },
    {
      label: 'مرجع',
      links: [
        { title: 'مرجع CLI', path: '/fa/reference' },
        { title: 'مرجع API', path: '/fa/api-reference' },
        { title: 'تغییرات', path: '/fa/changelog' }
      ]
    },
    {
      label: 'یادگیری',
      links: [
        { title: 'نمونه‌های استفاده', path: '/fa/usage-examples' },
        { title: 'راهنمای کامل ویژگی‌ها', path: '/fa/complete-features-guide' },
        { title: 'مستندات Swagger مبتنی بر Route', path: '/fa/route-based-swagger' }
      ]
    }
  ]

  return currentLang.value === 'fa' ? faNav : enNav
})

useHead({
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
          <NuxtLink to="/en" class="text-lg font-semibold text-primary-600">Laravel Scaffolder</NuxtLink>
          <span class="hidden text-sm text-slate-500 sm:inline">Docs</span>
        </div>
        <nav class="flex items-center gap-4 text-sm font-medium text-slate-600">
          <NuxtLink to="/en" class="hover:text-primary-600" :class="{ 'text-primary-600': currentLang === 'en' }">
            English</NuxtLink>
          <span class="text-slate-300">·</span>
          <NuxtLink to="/fa" class="hover:text-primary-600" :class="{ 'text-primary-600': currentLang === 'fa' }">فارسی
          </NuxtLink>
          <a href="https://github.com/AfshinEfati/Laravel-Scaffolder" target="_blank" rel="noopener"
            class="inline-flex items-center gap-1 rounded-md border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-600 hover:border-primary-500 hover:text-primary-600">
            <span>GitHub</span>
          </a>
        </nav>
      </div>
    </header>

    <div :class="contentContainerClasses">
      <aside v-if="!hideNavigation" class="lg:w-64">
        <div class="sticky top-24 space-y-8">
          <template v-for="section in navigation" :key="section.label">
            <div>
              <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-500"
                :class="{ 'text-right': isRtl }">{{ section.label }}</p>
              <ul class="space-y-2" :class="{ 'text-right': isRtl }">
                <li v-for="link in section.links" :key="link.path">
                  <NuxtLink :to="link.path"
                    class="block rounded-md px-3 py-2 text-sm transition hover:bg-primary-50 hover:text-primary-600"
                    :class="{
                      'bg-primary-100 text-primary-700 font-semibold': isActiveLink(link.path)
                    }">
                    {{ link.title }}
                  </NuxtLink>
                </li>
              </ul>
            </div>
          </template>
        </div>
      </aside>

      <main :class="['min-w-0 flex-1', hideNavigation ? 'w-full' : '']">
        <article class="prose prose-slate max-w-none"
          :class="{ 'prose-lg text-right': isRtl, 'prose-code:font-mono': true }">
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

.rtl .hero__content {
  text-align: right;
  align-items: flex-end;
}
</style>
