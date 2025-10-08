<script setup lang="ts">
import { computed } from 'vue'
import { createError } from 'h3'

const route = useRoute()
const langParam = Array.isArray(route.params.lang) ? route.params.lang[0] : (route.params.lang as string)
const contentPath = `/${langParam}`

const { data: doc } = await useAsyncData(`doc-${contentPath}`, () => queryContent(contentPath).findOne())

if (!doc.value) {
  throw createError({ statusCode: 404, statusMessage: 'Document not found' })
}

const pageTitle = computed(() =>
  doc.value?.title ? `${doc.value.title} · Laravel Module Generator` : 'Laravel Module Generator'
)

useHead(() => ({
  title: pageTitle.value
}))
</script>

<template>
  <ContentRenderer :value="doc" />
</template>
