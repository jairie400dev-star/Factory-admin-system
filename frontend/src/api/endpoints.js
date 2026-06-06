// Single source of truth for backend paths (relative to API_BASE_URL).
// Item routes are functions so callers pass the id: API_ROUTES.factories.item(3).
export const API_ROUTES = {
  auth: {
    login: '/auth/admin/login',
    logout: '/auth/logout',
    profile: '/auth/profile',
  },
  factories: {
    list: '/factories',
    item: (id) => `/factories/${id}`,
  },
  employees: {
    list: '/employees',
    item: (id) => `/employees/${id}`,
  },
  logs: {
    list: '/logs',
  },
  dashboard: {
    get: '/dashboard',
  },
}
