# Factory Admin — Operations Console

A responsive admin UI for managing factories and employees, built with
**Vue 3 + Vite + Tailwind CSS + Vue Router + Pinia** and talking to a Laravel +
Sanctum REST API.

## Screens

| Route | View |
| --- | --- |
| `/login` | Split-screen sign in (with "remember me") |
| `/dashboard` | Stats, recent activity, top factories by headcount |
| `/factories` | Factories table (search, pagination, view, delete) |
| `/factories/new`, `/factories/:id/edit` | Factory form + validation |
| `/employees` | Employees table (search, factory filter, pagination) |
| `/employees/new`, `/employees/:id/edit` | Employee form + validation |
| `/activity` | Activity log — Timeline + `laravel.log` views |

## Requirements

- **Node.js 18+**
- A running backend API (the Laravel app described in [docs/](docs/))

## Run it

```bash
npm install
npm run dev      # http://localhost:5173
npm run build    # production build to dist/
npm run preview  # preview the production build
```

Sign in with the seeded demo credentials: **admin@admin.com / password**.

## Configuration

Copy [.env.example](.env.example) to `.env`. The default uses the Vite dev proxy,
so all requests stay same-origin:

```env
VITE_API_BASE_URL=/api                 # base path for all requests
VITE_API_PROXY=http://localhost:8000   # backend the dev server proxies /api to
```

- For a production build, set `VITE_API_BASE_URL` to the real backend URL
  (e.g. `https://api.example.com/api`) — it is baked in at build time and the dev
  proxy does not apply.
- To reach the dev server from another device, run `npm run dev -- --host` and use
  the proxy form above so API calls are forwarded through Vite.

## API & auth

The app consumes the endpoints documented in [docs/API_DOCUMENTATION.md](docs/API_DOCUMENTATION.md):

| Method | Endpoint |
| --- | --- |
| `POST` | `/auth/admin/login`, `/auth/logout` · `GET /auth/profile` |
| `GET/POST` | `/factories`, `/factories/{id}` (+ `PUT`, `DELETE`) |
| `GET/POST` | `/employees`, `/employees/{id}` (+ `PUT`, `DELETE`) |
| `GET` | `/dashboard`, `/logs` |

- The auth token is stored in a cookie and sent as `Authorization: Bearer …`.
  "Remember me" makes it a 30-day cookie; otherwise a session cookie.
- A `401` clears the session and redirects to `/login`.
- Laravel `422` validation errors are mapped onto the matching form fields.

## Structure

```
src/
  api/         axios client, http wrapper, endpoints, resource services
  components/  Sidebar, Icon set, PageHeader, ConfirmDialog, Pagination, LazyItem, Toasts, BrandMark
  composables/ useToast, useResourceList, useResourceForm, useConfirmDelete, useCounts
  config/      env + app constants
  layouts/     AdminLayout (responsive sidebar + mobile drawer)
  router/      routes + auth guard
  stores/      auth (Pinia)
  utils/       storage/cookie helpers, formatting, debounce, validation
  views/       Login, Dashboard, Factories, Employees, Activity, forms
```

Fully responsive: the sidebar collapses into a slide-in drawer below `lg`, and
tables scroll horizontally on small screens.
