<script setup lang="ts">
import { queryContent } from '#imports'

const route = useRoute()
const langParam = Array.isArray(route.params.lang) ? route.params.lang[0] : (route.params.lang as string)
const slugParam = route.params.slug

// Build content path from slug segments
const slugSegments = Array.isArray(slugParam) ? slugParam : [slugParam]
const queryPath = `${langParam}/${slugSegments.join('/')}`

// Use static generation - fetch document at build time
const doc = await queryContent(queryPath).findOne()
</script>

<template>
  <NuxtLayout>
    <ContentRenderer :value="doc" />
  </NuxtLayout>
</template>
