<script setup lang="ts">
const route = useRoute()
const lang = Array.isArray(route.params.lang) ? route.params.lang[0] : (route.params.lang as string)
await navigateTo(`/${lang}/index`, { replace: true })
</script>

<template>
  <div />
</template>
