<script setup lang="ts">
import { queryContent, createError } from '#imports'

const route = useRoute()
const langParam = Array.isArray(route.params.lang) ? route.params.lang[0] : (route.params.lang as string)
const slugParam = route.params.slug

// Build content path from slug segments
const slugSegments = Array.isArray(slugParam) ? slugParam : [slugParam]
const contentPath = `/${langParam}/${slugSegments.join('/')}`
const queryPath = `${langParam}/${slugSegments.join('/')}`

// Try fetching by _path match
let doc = await queryContent().where({ _path: contentPath }).findOne()

// If not found, try queryContent with path string
if (!doc) {
  doc = await queryContent(queryPath).findOne()
}

// If still not found, search by prefix match
if (!doc) {
  const allDocs = await queryContent().find()
  const match = allDocs.find(d => d._path === contentPath || d._path === queryPath)
  doc = match || null
}

if (!doc) {
  throw createError({ statusCode: 404, statusMessage: 'Document not found' })
}

const { data } = await useAsyncData(`doc-${contentPath}`, () => Promise.resolve(doc))
</script>

<template>
  <NuxtLayout>
    <ContentRenderer :value="data || doc" />
  </NuxtLayout>
</template>
