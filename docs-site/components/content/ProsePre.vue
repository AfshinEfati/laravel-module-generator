<template>
  <div class="code-block-wrapper">
    <div class="code-block-header">
      <span v-if="language" class="code-language">{{ language }}</span>
      <button
        class="copy-button"
        :class="{ copied: copied }"
        @click="copyCode"
        :title="copied ? (copiedText || 'Copied!') : (copyText || 'Copy code')"
      >
        <svg v-if="!copied" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
          <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
        </svg>
        <svg v-else xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="20 6 9 17 4 12"></polyline>
        </svg>
      </button>
    </div>
    <pre :class="$props.class"><slot /></pre>
  </div>
</template>

<script setup lang="ts">
import { ref, useSlots, onMounted } from 'vue'

const props = defineProps({
  code: {
    type: String,
    default: ''
  },
  language: {
    type: String,
    default: null
  },
  filename: {
    type: String,
    default: null
  },
  highlights: {
    type: Array as () => number[],
    default: () => []
  },
  meta: {
    type: String,
    default: null
  },
  class: {
    type: String,
    default: ''
  }
})

const slots = useSlots()
const copied = ref(false)
const codeText = ref('')

// Extract text from slots
onMounted(() => {
  if (props.code) {
    codeText.value = props.code
  } else if (slots.default) {
    const slotContent = slots.default()
    codeText.value = extractTextFromVNode(slotContent)
  }
})

const extractTextFromVNode = (vnodes: any): string => {
  if (!vnodes) return ''
  
  let text = ''
  for (const vnode of vnodes) {
    if (typeof vnode.children === 'string') {
      text += vnode.children
    } else if (Array.isArray(vnode.children)) {
      text += extractTextFromVNode(vnode.children)
    } else if (vnode.children && typeof vnode.children === 'object') {
      if (vnode.children.default) {
        const defaultSlot = vnode.children.default()
        text += extractTextFromVNode(defaultSlot)
      }
    }
    if (vnode.props?.code) {
      text += vnode.props.code
    }
  }
  return text
}

const copyCode = async () => {
  try {
    const textToCopy = props.code || codeText.value
    await navigator.clipboard.writeText(textToCopy)
    copied.value = true
    setTimeout(() => {
      copied.value = false
    }, 2000)
  } catch (err) {
    console.error('Failed to copy code:', err)
  }
}

// Determine copy/copied text based on language
const copyText = ref('Copy')
const copiedText = ref('Copied!')

onMounted(() => {
  // Check if page is in Persian (RTL)
  const htmlLang = document.documentElement.getAttribute('lang')
  if (htmlLang === 'fa') {
    copyText.value = 'کپی'
    copiedText.value = 'کپی شد!'
  }
})
</script>

<style scoped>
.code-block-wrapper {
  position: relative;
  margin: 1.5rem 0;
}

.code-block-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem 1rem;
  background-color: #f1f1f1;
  border: 1px solid #e5e5e5;
  border-bottom: none;
  border-radius: 8px 8px 0 0;
  font-size: 0.75rem;
  font-weight: 500;
}

.code-language {
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  font-family: 'Fira Code', monospace;
  font-size: 0.7rem;
}

.copy-button {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.25rem;
  padding: 0.25rem 0.5rem;
  background-color: transparent;
  border: 1px solid #d1d5db;
  border-radius: 4px;
  color: #64748b;
  cursor: pointer;
  transition: all 0.2s ease;
  font-size: 0.7rem;
}

.copy-button:hover {
  background-color: #e5e7eb;
  border-color: #9ca3af;
  color: #374151;
}

.copy-button.copied {
  background-color: #dcfce7;
  border-color: #86efac;
  color: #16a34a;
}

.code-block-wrapper pre {
  margin: 0 !important;
  border-radius: 0 0 8px 8px !important;
  border-top: none !important;
}
</style>
