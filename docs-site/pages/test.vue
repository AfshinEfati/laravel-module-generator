<script setup lang="ts">
import { queryContent } from '#imports'

const allDocs = await queryContent().find()
const enDocs = await queryContent().where({ _path: /^\/en/ }).find()

console.log('ALL DOCS FOUND:', allDocs.length)
console.log('EN DOCS FOUND:', enDocs.length)

if (allDocs.length > 0) {
  console.log('First doc _path:', allDocs[0]._path)
  console.log('First doc:', allDocs[0].title, allDocs[0]._path)
}

if (enDocs.length > 0) {
  console.log('First en doc:', enDocs[0]._path, enDocs[0].title)
}
</script>

<template>
  <div style="padding: 20px; font-family: monospace; white-space: pre; background: #f5f5f5;">
ALL_DOCS_COUNT: {{ allDocs.length }}
EN_DOCS_COUNT: {{ enDocs.length }}

{{ allDocs.length > 0 ? `First doc: ${allDocs[0]._path}` : 'No docs found' }}

EN DOCS:
{{ enDocs.map(d => `${d._path}: ${d.title}`).join('\n') }}
  </div>
</template>
