<script setup>
// Renders the global toast queue (bottom-right). Mounted once in App.vue;
// other components trigger toasts via the useToast composable.
import { useToast } from '@/composables/useToast'
import AppIcon from './AppIcon.vue'

const { toasts, dismiss } = useToast()

// Colour scheme per toast type.
const tone = {
  success: 'border-emerald-200 bg-emerald-50 text-emerald-800',
  error: 'border-rose-200 bg-rose-50 text-rose-800',
  info: 'border-line bg-surface text-ink',
}
</script>

<template>
  <div class="pointer-events-none fixed bottom-5 right-5 z-50 flex w-80 flex-col gap-2">
    <transition-group name="toast">
      <div
        v-for="t in toasts"
        :key="t.id"
        class="pointer-events-auto flex items-start gap-3 rounded-xl border px-4 py-3 text-sm shadow-card"
        :class="tone[t.type]"
      >
        <AppIcon :name="t.type === 'error' ? 'x' : 'check'" :size="16" class="mt-0.5 shrink-0" />
        <span class="flex-1">{{ t.message }}</span>
        <button class="opacity-50 hover:opacity-100" @click="dismiss(t.id)">
          <AppIcon name="x" :size="14" />
        </button>
      </div>
    </transition-group>
  </div>
</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.25s ease;
}
.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateY(8px);
}
</style>
