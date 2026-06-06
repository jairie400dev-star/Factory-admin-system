<script setup>
// App logo + wordmark. Used in the sidebar and login screen.
defineProps({
  // 'light' for dark backgrounds, 'dark' for light backgrounds
  variant: { type: String, default: 'dark' },
  subtitle: { type: String, default: 'Operations console' },
})
</script>

<template>
  <div class="flex items-center gap-3">
    <span
      class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-primary text-white shadow-sm"
    >
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
        stroke-width="2.4" stroke-linecap="round">
        <path d="M12 5v14M5 12h14" />
      </svg>
    </span>
    <div class="leading-tight">
      <p
        class="font-serif text-[15px] font-semibold tracking-tight"
        :class="variant === 'light' ? 'text-white' : 'text-ink'"
      >
        Factory Admin
      </p>
      <p
        class="text-[11px]"
        :class="variant === 'light' ? 'text-white/45' : 'text-ink/45'"
      >
        {{ subtitle }}
      </p>
    </div>
  </div>
</template>
