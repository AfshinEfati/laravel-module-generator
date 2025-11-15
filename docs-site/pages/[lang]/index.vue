<script setup lang="ts">
import { queryContent, createError } from '#imports'

const route = useRoute()
const langParam = Array.isArray(route.params.lang) ? route.params.lang[0] : (route.params.lang as string)

// Try fetching by exact _path match
let doc = await queryContent().where({ _path: `/${langParam}` }).findOne()

// If not found, try looking for index.md in the directory
if (!doc) {
  doc = await queryContent(`${langParam}`).findOne()
}

// If still not found, try alternative paths
if (!doc) {
  const allDocs = await queryContent().find()
  const match = allDocs.find(d =>
    d._path === `/${langParam}` ||
    d._path === `${langParam}` ||
    d._dir === langParam ||
    d._file === 'index'
  )
  doc = match || null
}

if (!doc) {
  throw createError({ statusCode: 404, statusMessage: 'Document not found' })
}

const { data } = await useAsyncData(`doc-${langParam}-index`, () => Promise.resolve(doc))
</script>

<template>
  <NuxtLayout>
    <ContentRenderer :value="data || doc" />
  </NuxtLayout>
</template>
