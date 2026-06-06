import { http } from './http'
import { API_ROUTES } from './endpoints'

// Resource services: one object per backend resource, returning plain data to the views.
// Laravel responses are wrapped, e.g. { success, pagination, data } — these helpers unwrap them.

// Pull the payload out of a single-object response ({ data: {...} } or the object itself).
const unwrap = (res) => res.data?.data ?? res.data
// Normalize a paginated list response into a consistent shape for the views.
const unwrapPaged = (res) => ({
  data: res.data?.data ?? res.data,
  pagination: res.data?.pagination ?? null,
  factories: res.data?.factories ?? null,
  success: res.data?.success ?? null,
})

// Fetch every page of a paginated resource and return a flat array (for dropdowns, etc.).
async function fetchAll(listFn, params = {}) {
  const first = await listFn({ ...params, page: 1 })
  const totalPages = first.pagination?.total_page ?? 1
  const all = [...(first.data ?? [])]
  if (totalPages > 1) {
    const rest = await Promise.all(
      Array.from({ length: totalPages - 1 }, (_, i) => listFn({ ...params, page: i + 2 })),
    )
    rest.forEach((r) => all.push(...(r.data ?? [])))
  }
  return all
}

export const factoriesApi = {
  list: (params = {}) => http.get(API_ROUTES.factories.list, params).then(unwrapPaged),
  listAll: (params = {}) => fetchAll(factoriesApi.list, params),
  get: (id) => http.get(API_ROUTES.factories.item(id)).then(unwrap),
  create: (payload) => http.post(API_ROUTES.factories.list, payload).then(unwrap),
  update: (id, payload) => http.put(API_ROUTES.factories.item(id), payload).then(unwrap),
  remove: (id) => http.delete(API_ROUTES.factories.item(id)).then(() => true),
}

export const employeesApi = {
  list: (params = {}) => http.get(API_ROUTES.employees.list, params).then(unwrapPaged),
  listAll: (params = {}) => fetchAll(employeesApi.list, params),
  get: (id) => http.get(API_ROUTES.employees.item(id)).then(unwrap),
  create: (payload) => http.post(API_ROUTES.employees.list, payload).then(unwrap),
  update: (id, payload) => http.put(API_ROUTES.employees.item(id), payload).then(unwrap),
  remove: (id) => http.delete(API_ROUTES.employees.item(id)).then(() => true),
}

export const activityApi = {
  list: (params) => http.get(API_ROUTES.logs.list, params).then(unwrap),
}

export const dashboardApi = {
  get: () => http.get(API_ROUTES.dashboard.get).then(unwrap),
}
