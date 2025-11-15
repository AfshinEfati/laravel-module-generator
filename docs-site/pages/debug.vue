<script setup lang="ts">
import { queryContent } from '#imports'

const allDocs = await queryContent().find()
const enDocs = await queryContent().where({ _path: /^\/en/ }).find()
const indexDoc = await queryContent().where({ _path: '/en' }).findOne()

console.log('ALL DOCS:', allDocs)
console.log('EN DOCS:', enDocs)
console.log('INDEX DOC:', indexDoc)
</script>

<template>
  <div style="padding: 20px; font-family: monospace; background: #f5f5f5;">
    <h1>Debug: Content Query Results</h1>
    <pre>{{ { allDocs: allDocs.length, enDocs: enDocs.length, indexDoc } }}</pre>
  </div>
</template>
