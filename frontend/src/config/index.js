// App-wide configuration and constants. Values that vary per environment come
// from Vite env vars (VITE_*); everything else is a shared constant.
export const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || '/api'

export const STORAGE_KEYS = {
  AUTH_TOKEN: 'auth_token',
  AUTH_USER: 'auth_user',
  REMEMBER_EMAIL: 'remember_email',
}

// How long the "remember me" auth-token cookie persists (in days).
export const REMEMBER_DAYS = 30

export const ROUTE_NAMES = {
  LOGIN: 'login',
  DASHBOARD: 'dashboard',
  FACTORIES: 'factories',
  FACTORY_CREATE: 'factory-create',
  FACTORY_EDIT: 'factory-edit',
  EMPLOYEES: 'employees',
  EMPLOYEE_CREATE: 'employee-create',
  EMPLOYEE_EDIT: 'employee-edit',
  ACTIVITY: 'activity',
}

export const DEFAULTS = {
  USER_NAME: 'Admin',
  USER_EMAIL: 'admin@admin.com',
}

export const URL_PATHS = {
  LOGIN: '/login',
}

export const HTTP_HEADERS = {
  ACCEPT: 'application/json',
  CONTENT_TYPE: 'application/json',
}
