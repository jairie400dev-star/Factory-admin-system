import client from './client'

// Thin verb-based wrapper over the axios client so resource services read cleanly
// (http.get/post/put/delete) without repeating axios config everywhere.
export const http = {
  get: (url, params) => client.get(url, { params }),
  post: (url, data) => client.post(url, data),
  put: (url, data) => client.put(url, data),
  delete: (url, config) => client.delete(url, config),
}
