<script setup>
// Standard page heading: optional eyebrow + title + subtitle, with an #actions slot
// for buttons on the right (e.g. "New factory").
defineProps({
  eyebrow: { type: String, default: '' },
  title: { type: String, required: true },
  subtitle: { type: String, default: '' },
})
</script>

<template>
  <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
      <p v-if="eyebrow" class="eyebrow mb-1">{{ eyebrow }}</p>
      <h1 class="font-serif text-3xl font-semibold tracking-tight text-ink sm:text-4xl">
        {{ title }}
      </h1>
      <p v-if="subtitle" class="mt-1.5 max-w-xl text-sm text-ink/55">{{ subtitle }}</p>
    </div>
    <div class="shrink-0">
      <slot name="actions" />
    </div>
  </div>
</template>
