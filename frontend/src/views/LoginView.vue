<script setup>
// Admin sign-in screen. "Remember me" both keeps the session persistent (vs. session-only)
// and pre-fills the email on the next visit.
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { ROUTE_NAMES, STORAGE_KEYS } from '@/config'
import { getStorage, setStorage, removeStorage } from '@/utils'
import BrandMark from '@/components/BrandMark.vue'
import AppIcon from '@/components/AppIcon.vue'

const router = useRouter()
const route = useRoute()
const auth = useAuthStore()

const rememberedEmail = getStorage(STORAGE_KEYS.REMEMBER_EMAIL)
const email = ref(rememberedEmail)
const password = ref('')
const remember = ref(!!rememberedEmail)
const loading = ref(false)
const error = ref('')

async function submit() {
  error.value = ''
  loading.value = true
  try {
    await auth.login({ email: email.value, password: password.value, remember: remember.value })
    // Remember the email for next time (but never the password).
    if (remember.value) setStorage(STORAGE_KEYS.REMEMBER_EMAIL, email.value)
    else removeStorage(STORAGE_KEYS.REMEMBER_EMAIL)
    router.push(route.query.redirect || { name: ROUTE_NAMES.DASHBOARD })
  } catch (e) {
    error.value = e?.message || 'Unable to sign in.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="grid min-h-screen lg:grid-cols-2">
    <!-- Form panel -->
    <div class="flex items-center justify-center bg-canvas px-6 py-12">
      <div class="w-full max-w-sm">
        <BrandMark class="mb-10" />

        <h1 class="font-serif text-4xl font-semibold tracking-tight text-ink">Welcome back</h1>
        <p class="mt-2 text-sm text-ink/55">Sign in to manage factories and employees.</p>

        <form class="mt-8 space-y-5" @submit.prevent="submit">
          <div
            v-if="error"
            class="rounded-lg border border-rose-200 bg-rose-50 px-3.5 py-2.5 text-sm text-rose-700"
          >
            {{ error }}
          </div>

          <div>
            <label class="field-label" for="email">Email address</label>
            <input
              id="email"
              v-model="email"
              type="email"
              autocomplete="email"
              required
              class="field-input"
              placeholder="you@example.com"
            />
          </div>

          <div>
            <div class="mb-1.5 flex items-center justify-between">
              <label class="field-label !mb-0" for="password">Password</label>
              <a href="#" class="text-sm font-medium text-primary-600 hover:text-primary-700">
                Forgot password?
              </a>
            </div>
            <input
              id="password"
              v-model="password"
              type="password"
              autocomplete="current-password"
              required
              class="field-input"
              placeholder="••••••••"
            />
          </div>

          <label class="flex items-center gap-2.5 text-sm text-ink/70">
            <input
              v-model="remember"
              type="checkbox"
              class="h-4 w-4 rounded border-line text-primary focus:ring-primary-300"
            />
            Remember me
          </label>

          <button type="submit" class="btn-primary w-full" :disabled="loading">
            {{ loading ? 'Signing in…' : 'Sign in' }}
          </button>
        </form>

        <p class="mt-6 text-center text-xs leading-relaxed text-ink/40">
          Registration is disabled. Accounts are provisioned by an administrator.
        </p>
      </div>
    </div>

    <!-- Promo panel -->
    <div class="relative hidden overflow-hidden bg-ink-900 px-12 py-16 text-white lg:flex lg:flex-col lg:justify-center">
      <!-- Decorative spark -->
      <svg class="absolute right-0 top-0 h-72 w-72 text-white/[0.04]" viewBox="0 0 200 200" fill="currentColor">
        <path d="M100 0c6 60 34 88 100 100-66 12-94 40-100 100-6-60-34-88-100-100C66 88 94 60 100 0Z" />
      </svg>

      <div class="relative max-w-md">
        <p class="eyebrow !text-primary-300">Operations console</p>
        <h2 class="mt-4 font-serif text-4xl font-semibold leading-[1.15] tracking-tight">
          Every factory, every employee — managed in one place.
        </h2>
        <p class="mt-5 text-base leading-relaxed text-white/55">
          Create and edit records, track every change, and keep your manufacturing
          network in order.
        </p>
      </div>
    </div>
  </div>
</template>
