<template>
  <span :class="$props.class">{{ code }}</span>
</template>

<script setup lang="ts">
defineProps({
  code: {
    type: String,
    default: ''
  },
  language: {
    type: String,
    default: null
  },
  class: {
    type: String,
    default: ''
  }
})
</script>
