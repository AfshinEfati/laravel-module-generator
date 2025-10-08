<script setup lang="ts">
import { computed, watchEffect } from 'vue'
import { useState } from '#imports'
import { createError } from 'h3'

const route = useRoute()
const langParam = Array.isArray(route.params.lang) ? route.params.lang[0] : (route.params.lang as string)
const contentPath = `/${langParam}`

const { data: doc } = await useAsyncData(`doc-${contentPath}`, () => queryContent(contentPath).findOne())

if (!doc.value) {
  throw createError({ statusCode: 404, statusMessage: 'Document not found' })
}

const hideNavigationState = useState<boolean>('hide-navigation', () => false)

const resolveHideNavigation = (hide: unknown): boolean => {
  if (!hide) {
    return false
  }

  if (Array.isArray(hide)) {
    return hide.includes('navigation')
  }

  if (typeof hide === 'string') {
    return hide === 'navigation'
  }

  if (typeof hide === 'object') {
    return Boolean((hide as Record<string, unknown>).navigation)
  }

  if (typeof hide === 'boolean') {
    return hide
  }

  return false
}

watchEffect(() => {
  hideNavigationState.value = resolveHideNavigation(doc.value?.hide)
})

const pageTitle = computed(() =>
  doc.value?.title ? `${doc.value.title} · Laravel Module Generator` : 'Laravel Module Generator'
)

useHead(() => ({
  title: pageTitle.value
}))
</script>

<template>
  <NuxtLayout :lang="langParam" :doc="doc">
    <ContentRenderer :value="doc" />
  </NuxtLayout>
</template>
