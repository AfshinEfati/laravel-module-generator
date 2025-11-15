<script setup lang="ts">
import { queryContent } from '#imports'

const route = useRoute()
const langParam = Array.isArray(route.params.lang) ? route.params.lang[0] : (route.params.lang as string)

// Use static generation - fetch document at build time
const doc = await queryContent(langParam).findOne()
</script>

<template>
  <NuxtLayout>
    <ContentRenderer :value="doc" />
  </NuxtLayout>
</template>
