<script setup>
// Root component: renders the active route plus the global toast host.
import ToastHost from '@/components/ToastHost.vue'
</script>

<template>
  <router-view />
  <ToastHost />
</template>
