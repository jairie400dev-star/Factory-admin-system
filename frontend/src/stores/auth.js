import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { http } from '@/api/http'
import { API_ROUTES } from '@/api/endpoints'
import { DEFAULTS, STORAGE_KEYS, REMEMBER_DAYS } from '@/config'
import {
  getCookie,
  getJsonStorage,
  removeCookie,
  removeStorage,
  setCookie,
  setJsonStorage,
} from '@/utils'

// Authentication store: holds the bearer token + current user.
// The token lives in a cookie (persistent when "remember me" is on, otherwise a
// session cookie); the user profile is mirrored to local/session storage so it's
// available on first paint before the profile refetch.
export const useAuthStore = defineStore('auth', () => {
  const token = ref(getCookie(STORAGE_KEYS.AUTH_TOKEN))
  // Remember = the profile was stored persistently (localStorage) alongside the token.
  const remember = ref(!!getJsonStorage(STORAGE_KEYS.AUTH_USER))
  const user = ref(
    getJsonStorage(STORAGE_KEYS.AUTH_USER) ?? getJsonStorage(STORAGE_KEYS.AUTH_USER, null, true),
  )

  const isAuthenticated = computed(() => !!token.value)

  function persist() {
    const session = !remember.value
    // Token → cookie: persistent (REMEMBER_DAYS) when remembering, else a session cookie.
    removeCookie(STORAGE_KEYS.AUTH_TOKEN)
    if (token.value) setCookie(STORAGE_KEYS.AUTH_TOKEN, token.value, remember.value ? REMEMBER_DAYS : 0)
    // User → web storage (clear both first so it never lingers in the wrong store).
    removeStorage(STORAGE_KEYS.AUTH_USER)
    removeStorage(STORAGE_KEYS.AUTH_USER, true)
    if (user.value) setJsonStorage(STORAGE_KEYS.AUTH_USER, user.value, session)
  }

  async function login({ email, password, remember: rememberMe = true }) {
    remember.value = rememberMe
    // Documented backend: POST /auth/admin/login -> { status, message, data: { access_token } }
    // Any network/validation error simply propagates to the caller (LoginView).
    const res = await http.post(API_ROUTES.auth.login, { email, password })
    if (res.data?.status === false) {
      throw { status: 401, message: res.data?.message || 'Invalid credentials.' }
    }
    token.value = res.data?.data?.access_token || ''
    user.value = { name: DEFAULTS.USER_NAME, email }
    persist()
    // Pull the real profile (name, email, role) in the background now that we have a token.
    fetchProfile()
    return user.value
  }

  async function fetchProfile() {
    if (!token.value) return null
    try {
      const res = await http.get(API_ROUTES.auth.profile)
      const data = res.data?.data ?? res.data
      if (data) {
        user.value = data
        persist()
      }
    } catch {
      /* ignore — keep whatever user we have */
    }
    return user.value
  }

  async function logout() {
    try {
      await http.post(API_ROUTES.auth.logout)
    } catch {
      /* ignore — clear locally regardless */
    }
    token.value = ''
    user.value = null
    persist()
  }

  return { token, user, isAuthenticated, login, logout, fetchProfile }
})
