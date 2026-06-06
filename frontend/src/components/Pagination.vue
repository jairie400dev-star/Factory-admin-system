<script setup>
import { computed } from 'vue'

const props = defineProps({
  modelValue: { type: Number, default: 1 },
  totalPages: { type: Number, default: 1 },
  // Max numbered buttons to show at once (the "limited window").
  max: { type: Number, default: 5 },
})
const emit = defineEmits(['update:modelValue'])

const pages = computed(() => {
  const total = Math.max(1, props.totalPages)
  const current = Math.min(Math.max(1, props.modelValue), total)
  const max = Math.max(3, props.max)
  if (total <= max) return Array.from({ length: total }, (_, i) => i + 1)

  const half = Math.floor(max / 2)
  let start = Math.max(1, current - half)
  const end = Math.min(total, start + max - 1)
  start = Math.max(1, end - max + 1)

  const result = []
  if (start > 1) {
    result.push(1)
    if (start > 2) result.push('…')
  }
  for (let p = start; p <= end; p++) result.push(p)
  if (end < total) {
    if (end < total - 1) result.push('…')
    result.push(total)
  }
  return result
})

function go(p) {
  if (typeof p !== 'number') return
  if (p < 1 || p > props.totalPages || p === props.modelValue) return
  emit('update:modelValue', p)
}
</script>

<template>
  <div class="flex items-center gap-1 text-sm text-ink/60">
    <button
      class="rounded-md px-3 py-1 transition-colors hover:bg-canvas disabled:opacity-40 disabled:hover:bg-transparent"
      :disabled="modelValue <= 1"
      @click="go(modelValue - 1)"
    >
      Prev
    </button>

    <template v-for="(p, i) in pages" :key="`${p}-${i}`">
      <span v-if="p === '…'" class="px-2 text-ink/40">…</span>
      <button
        v-else
        class="min-w-8 rounded-md px-2.5 py-1 transition-colors"
        :class="p === modelValue ? 'bg-primary-600 font-semibold text-white' : 'hover:bg-canvas'"
        @click="go(p)"
      >
        {{ p }}
      </button>
    </template>

    <button
      class="rounded-md px-3 py-1 transition-colors hover:bg-canvas disabled:opacity-40 disabled:hover:bg-transparent"
      :disabled="modelValue >= totalPages"
      @click="go(modelValue + 1)"
    >
      Next
    </button>
  </div>
</template>
