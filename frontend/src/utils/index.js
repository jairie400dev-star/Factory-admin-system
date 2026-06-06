// Small, dependency-free helpers shared across the app: safe Web Storage access,
// text search, and formatting. All storage helpers swallow errors so the UI never
// breaks in browsers with storage disabled.

// Pass session=true to use sessionStorage (cleared when the browser closes).
const backend = (session) => (session ? window.sessionStorage : window.localStorage)

export const getStorage = (key, defaultValue = '', session = false) => {
  try {
    const value = backend(session).getItem(key)
    return value !== null ? value : defaultValue
  } catch {
    return defaultValue
  }
}

export const setStorage = (key, value, session = false) => {
  try {
    backend(session).setItem(key, String(value))
  } catch {
    // Ignore storage failures in browsers with restricted storage.
  }
}

export const removeStorage = (key, session = false) => {
  try {
    backend(session).removeItem(key)
  } catch {
    // Ignore storage failures.
  }
}

export const getJsonStorage = (key, defaultValue = null, session = false) => {
  try {
    const value = backend(session).getItem(key)
    return value ? JSON.parse(value) : defaultValue
  } catch {
    return defaultValue
  }
}

export const setJsonStorage = (key, value, session = false) => {
  try {
    backend(session).setItem(key, JSON.stringify(value))
  } catch {
    // Ignore storage failures.
  }
}

// Returns a debounced version of `fn`: it only runs once calls stop for `delay` ms.
// Useful for search inputs so we don't hit the API on every keystroke.
// Call `.cancel()` on the returned function to drop a pending call.
export const debounce = (fn, delay = 300) => {
  let timer
  const debounced = (...args) => {
    clearTimeout(timer)
    timer = setTimeout(() => fn(...args), delay)
  }
  debounced.cancel = () => clearTimeout(timer)
  return debounced
}

// --- Cookies -------------------------------------------------------------
// Used for the auth token. Note: readable by JS (not HttpOnly) because the app
// sends it as a Bearer header; pass days for a persistent cookie, omit for a
// session cookie (cleared when the browser closes).

export const getCookie = (name) => {
  const escaped = name.replace(/([.$?*|{}()[\]\\/+^])/g, '\\$1')
  const match = document.cookie.match(new RegExp('(?:^|; )' + escaped + '=([^;]*)'))
  return match ? decodeURIComponent(match[1]) : ''
}

export const setCookie = (name, value, days = 0) => {
  let cookie = `${name}=${encodeURIComponent(value)}; path=/; SameSite=Lax`
  if (days) {
    const expires = new Date(Date.now() + days * 86400000).toUTCString()
    cookie += `; expires=${expires}`
  }
  if (window.location.protocol === 'https:') cookie += '; Secure'
  document.cookie = cookie
}

export const removeCookie = (name) => {
  document.cookie = `${name}=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT; SameSite=Lax`
}

// Basic email shape check for client-side form validation (backend validates too).
export const isValidEmail = (value) => /^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(String(value))

export const formatShortDateTime = (value) => {
  if (!value) return ''
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return String(value)
  return date.toLocaleString(undefined, {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: 'numeric',
    minute: '2-digit',
  })
}

export const toInitials = (value) => {
  if (!value) return ''
  const parts = String(value).trim().split(/\s+/)
  if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase()
  return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
}
