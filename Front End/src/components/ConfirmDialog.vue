<script setup>
// Reusable confirmation modal (delete, sign out, etc.). Teleported to <body> so it
// always overlays the page, and locks page scroll while open.
import { watch, onBeforeUnmount } from 'vue'
import AppIcon from './AppIcon.vue'

const props = defineProps({
  open: Boolean,
  title: { type: String, default: 'Are you sure?' },
  message: { type: String, default: '' },
  confirmLabel: { type: String, default: 'Delete' },
  busy: Boolean,
})
defineEmits(['confirm', 'cancel'])

// Lock page scroll while the dialog is open.
watch(
  () => props.open,
  (open) => {
    document.body.style.overflow = open ? 'hidden' : ''
  },
)

onBeforeUnmount(() => {
  document.body.style.overflow = ''
})
</script>

<template>
  <Teleport to="body">
    <transition name="fade">
      <div v-if="open" class="fixed inset-0 z-50 grid place-items-center p-4">
        <div class="absolute inset-0 bg-ink/40 backdrop-blur-sm" @click="$emit('cancel')" />
        <div class="relative w-full max-w-sm rounded-2xl border border-line bg-surface p-6 shadow-card">
          <div class="mb-4 grid h-11 w-11 place-items-center rounded-full bg-rose-50 text-rose-600">
            <AppIcon name="trash" :size="20" />
          </div>
          <h3 class="font-serif text-xl font-semibold text-ink">{{ title }}</h3>
          <p v-if="message" class="mt-1.5 text-sm text-ink/60">{{ message }}</p>
          <div class="mt-6 flex justify-end gap-2">
            <button class="btn-outline" :disabled="busy" @click="$emit('cancel')">Cancel</button>
            <button
              class="btn bg-rose-600 text-white hover:bg-rose-700"
              :disabled="busy"
              @click="$emit('confirm')"
            >
              {{ busy ? 'Working…' : confirmLabel }}
            </button>
          </div>
        </div>
      </div>
    </transition>
  </Teleport>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
