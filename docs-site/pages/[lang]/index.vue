<script setup lang="ts">
import { queryContent } from '#content/server'
import { createError } from 'h3'

const route = useRoute()
const langParam = Array.isArray(route.params.lang) ? route.params.lang[0] : (route.params.lang as string)
const contentPath = `/${langParam}`

const { data: doc } = await useAsyncData(`doc-${contentPath}`, () => queryContent(contentPath).findOne())

if (!doc.value) {
  throw createError({ statusCode: 404, statusMessage: 'Document not found' })
}
</script>

<template>
  <NuxtLayout :lang="langParam" :doc="doc">
    <ContentRenderer :value="doc" />
  </NuxtLayout>
</template>
