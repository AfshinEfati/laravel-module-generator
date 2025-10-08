<script setup lang="ts">
import { queryContent } from '#content'
import { createError } from 'nuxt/app'

const route = useRoute()
const langParam = Array.isArray(route.params.lang) ? route.params.lang[0] : (route.params.lang as string)
const slugParam = route.params.slug
const slug = Array.isArray(slugParam) ? slugParam.join('/') : (slugParam as string | undefined)
const path = `${langParam}/${slug ?? 'index'}`

const { data: doc } = await useAsyncData(`doc-${path}`, () => queryContent(path).findOne())

if (!doc.value) {
  throw createError({ statusCode: 404, statusMessage: 'Document not found' })
}
</script>

<template>
  <NuxtLayout :lang="langParam" :doc="doc">
    <ContentRenderer :value="doc" />
  </NuxtLayout>
</template>
