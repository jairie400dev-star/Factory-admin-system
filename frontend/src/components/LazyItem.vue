<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
  // How far outside the viewport to start rendering (overscan).
  rootMargin: { type: String, default: '400px' },
  // Reserved height before the item has ever been measured.
  minHeight: { type: Number, default: 120 },
})

const el = ref(null)
const visible = ref(false)
const height = ref(props.minHeight)
let observer

onMounted(() => {
  observer = new IntersectionObserver(
    ([entry]) => {
      if (entry.isIntersecting) {
        visible.value = true
      } else {
        // Cache the rendered height so the placeholder reserves the right space.
        if (visible.value && el.value) height.value = el.value.offsetHeight || height.value
        visible.value = false
      }
    },
    { rootMargin: props.rootMargin },
  )
  observer.observe(el.value)
})

onBeforeUnmount(() => observer?.disconnect())
</script>

<template>
  <div ref="el" :style="visible ? null : { minHeight: `${height}px` }">
    <slot v-if="visible" />
  </div>
</template>
