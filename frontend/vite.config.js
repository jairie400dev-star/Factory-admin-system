import { fileURLToPath, URL } from 'node:url'
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

// https://vite.dev/config/
export default defineConfig({
  plugins: [vue()],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },
  server: {
    port: 5173,
    // Proxy /api to a backend during development so cookies / same-origin work.
    // Point VITE_API_PROXY at your Laravel app (e.g. http://localhost:8000).
    proxy: process.env.VITE_API_PROXY
      ? { '/api': { target: process.env.VITE_API_PROXY, changeOrigin: true } }
      : undefined,
  },
})
