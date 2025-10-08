<script setup lang="ts">
import { queryContent, createError } from '#imports'

const route = useRoute()
const langParam = Array.isArray(route.params.lang) ? route.params.lang[0] : (route.params.lang as string)
const slugParam = route.params.slug
const slugSegments = Array.isArray(slugParam)
  ? slugParam
  : typeof slugParam === 'string'
    ? [slugParam]
    : []

if (slugSegments.length === 0) {
  throw createError({ statusCode: 404, statusMessage: 'Document not found' })
}

const normalizedSegments = slugSegments.length === 1 && slugSegments[0] === 'index'
  ? []
  : slugSegments

const contentPath = `/${langParam}${normalizedSegments.length ? '/' + normalizedSegments.join('/') : ''}`

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
