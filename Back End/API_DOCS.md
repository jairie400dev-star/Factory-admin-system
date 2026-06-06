# TMS API Documentation

REST API for the Transport/Factory Management System (factories, employees, dashboard metrics, and activity logs).

- **Base URL:** `http://127.0.0.1:8000/api`
- **Format:** All requests and responses use JSON. Always send `Accept: application/json`.
- **Auth:** Token-based (Laravel Sanctum). Obtain a token via the login endpoint and send it as a Bearer token.

---

## Table of Contents

1. [Authentication & Conventions](#authentication--conventions)
2. [Response Envelopes](#response-envelopes)
3. [Error Responses](#error-responses)
4. [Auth Endpoints](#auth-endpoints)
   - [Admin Login](#post-apiauthadminlogin)
   - [Get Profile](#get-apiauthprofile)
   - [Logout](#post-apiauthlogout)
5. [Employee Endpoints](#employee-endpoints)
6. [Factory Endpoints](#factory-endpoints)
7. [Dashboard](#get-apidashboard)
8. [Activity Logs](#get-apilogs)

---

## Authentication & Conventions

Every endpoint except **Admin Login** requires a valid Bearer token. All resource endpoints
(`employees`, `factories`, `dashboard`, `logs`) additionally require the user to be an
**administrator**.

Send the token on each protected request:

```http
Authorization: Bearer 1|abcDEF123yourTokenHere...
Accept: application/json
```

| Header | Value | Notes |
|---|---|---|
| `Authorization` | `Bearer <access_token>` | Required for all protected routes. |
| `Accept` | `application/json` | Ensures JSON error responses instead of HTML. |
| `Content-Type` | `application/json` | Required for `POST`/`PUT`/`PATCH` with a body. |

Tokens expire **3 hours** after login.

---

## Response Envelopes

The API uses **two** envelope styles depending on the endpoint group.

**Auth endpoints** (`status` key):

```json
{
  "status": true,
  "message": "Success",
  "data": { }
}
```

**Resource endpoints** — employees, factories, dashboard, logs (`success` key):

```json
{
  "success": true,
  "data": { }
}
```

---

## Error Responses

| HTTP | When | Body |
|---|---|---|
| `401 Unauthorized` | Missing/invalid token | `{ "message": "Unauthenticated." }` |
| `401 Unauthorized` | Bad login credentials | `{ "status": false, "message": "Invalid credentials or unauthorized.", "data": null }` |
| `403 Forbidden` | Authenticated but not an admin | `{ "message": "Forbidden. Admin access required." }` |
| `422 Unprocessable Entity` | Validation failed | See below |
| `500 Internal Server Error` | Unexpected failure | `{ "success": false, "message": "Unable to ..." }` |

**Validation error (422) example:**

```json
{
  "message": "The firstname field is required.",
  "errors": {
    "firstname": ["The firstname field is required."],
    "factory_id": ["The selected factory id is invalid."]
  }
}
```

---

## Auth Endpoints

### `POST /api/auth/admin/login`

Authenticate an administrator and receive an access token. **Public** (no token required).

**Request body**

| Field | Type | Rules |
|---|---|---|
| `email` | string | required, valid email |
| `password` | string | required |

```json
{
  "email": "admin@admin.com",
  "password": "password"
}
```

**Success — `200 OK`**

```json
{
  "status": true,
  "message": "Success",
  "data": {
    "access_token": "1|JdF8k...long-token-string",
    "token_type": "Bearer",
    "expires_at": "2026-06-06 03:20:47"
  }
}
```

**Failure — `401 Unauthorized`** (wrong password, unknown email, or non-admin user)

```json
{
  "status": false,
  "message": "Invalid credentials or unauthorized.",
  "data": null
}
```

**cURL**

```bash
curl -X POST http://127.0.0.1:8000/api/auth/admin/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@admin.com","password":"password"}'
```

---

### `GET /api/auth/profile`

Return the authenticated user's details, resolved from the access token. Requires a valid token.

**Success — `200 OK`**

```json
{
  "status": true,
  "message": "Success",
  "data": {
    "id": 1,
    "name": "Admin",
    "email": "admin@admin.com"
  }
}
```

**cURL**

```bash
curl http://127.0.0.1:8000/api/auth/profile \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <access_token>"
```

---

### `POST /api/auth/logout`

Revoke the current access token. Requires a valid token.

**Success — `200 OK`**

```json
{
  "status": true,
  "message": "Success",
  "data": {
    "message": "Logged out successfully."
  }
}
```

If there was no active token to revoke, `data.message` is `"No token to revoke."`.

---

## Employee Endpoints

All employee routes require an **admin** Bearer token.

The employee object returned everywhere:

```json
{
  "id": 13,
  "firstname": "Efren",
  "lastname": "Cremin",
  "factory_id": 5,
  "factory_name": "Robel, Cronin and Wiza Factory",
  "email": "timothy.wunsch@example.net",
  "phone": "+17244136563"
}
```

> `factory_name` is resolved from `factory_id`. It is `null` if the employee's factory was deleted.

---

### `GET /api/employees`

Paginated list of employees, **newest first**. Also returns the full factory list for dropdowns.

**Query parameters**

| Param | Type | Description |
|---|---|---|
| `search` | string | Partial match on `firstname` **or** `lastname`. |
| `factory_id` | integer | Restrict to a single factory. |
| `page` | integer | Page number (10 per page). |

**Success — `200 OK`**

```json
{
  "success": true,
  "pagination": {
    "total": 1091,
    "current_page": 1,
    "total_page": 110
  },
  "data": [
    {
      "id": 1129,
      "firstname": "Maria",
      "lastname": "Santos",
      "factory_id": 5,
      "factory_name": "Robel, Cronin and Wiza Factory",
      "email": "maria.santos@example.net",
      "phone": "+1987654321"
    }
  ],
  "factories": [
    { "id": 50, "factory_name": "Aufderhar, Johnston and Wunsch Factory" },
    { "id": 45, "factory_name": "Beatty-Metz Factory" }
  ]
}
```

**Example:** `GET /api/employees?search=maria&factory_id=5&page=2`

---

### `POST /api/employees`

Create an employee.

**Request body**

| Field | Type | Rules |
|---|---|---|
| `firstname` | string | required, max 255 |
| `lastname` | string | required, max 255 |
| `factory_id` | integer | required, must exist in `factories` |
| `email` | string | nullable, valid email, max 255 |
| `phone` | string | nullable, max 50 |

```json
{
  "firstname": "Maria",
  "lastname": "Santos",
  "factory_id": 5,
  "email": "maria.santos@example.net",
  "phone": "+1987654321"
}
```

**Success — `201 Created`**

```json
{
  "success": true,
  "data": {
    "id": 1130,
    "firstname": "Maria",
    "lastname": "Santos",
    "factory_id": 5,
    "factory_name": "Robel, Cronin and Wiza Factory",
    "email": "maria.santos@example.net",
    "phone": "+1987654321"
  }
}
```

---

### `GET /api/employees/{id}`

Fetch a single employee.

**Success — `200 OK`**

```json
{
  "success": true,
  "data": {
    "id": 13,
    "firstname": "Efren",
    "lastname": "Cremin",
    "factory_id": 5,
    "factory_name": "Robel, Cronin and Wiza Factory",
    "email": "timothy.wunsch@example.net",
    "phone": "+17244136563"
  }
}
```

A non-existent id returns `404 Not Found`.

---

### `PUT/PATCH /api/employees/{id}`

Update an employee. Same body and rules as create.

**Success — `200 OK`**

```json
{
  "success": true,
  "data": {
    "id": 13,
    "firstname": "Efren",
    "lastname": "Cremin-Updated",
    "factory_id": 6,
    "factory_name": "Hodkiewicz Group Factory",
    "email": "efren@example.net",
    "phone": "+17244136563"
  }
}
```

---

### `DELETE /api/employees/{id}`

Delete an employee.

**Success — `200 OK`**

```json
{
  "success": true,
  "message": "Employee deleted successfully."
}
```

---

## Factory Endpoints

All factory routes require an **admin** Bearer token.

The factory object returned everywhere:

```json
{
  "id": 5,
  "factory_name": "Robel, Cronin and Wiza Factory",
  "location": "Quezon City",
  "email": "contact@robel.example",
  "website": "https://robel.example",
  "employees_count": 33
}
```

---

### `GET /api/factories`

Paginated list of factories, **newest first**. Each item includes its employee headcount.

**Query parameters**

| Param | Type | Description |
|---|---|---|
| `search` | string | Partial match on `factory_name` **or** `location`. |
| `page` | integer | Page number (10 per page). |

**Success — `200 OK`**

```json
{
  "success": true,
  "pagination": {
    "total": 61,
    "current_page": 1,
    "total_page": 7
  },
  "data": [
    {
      "id": 66,
      "factory_name": "New Horizon Factory",
      "location": "Cebu City",
      "email": "info@newhorizon.example",
      "website": "https://newhorizon.example",
      "employees_count": 0
    }
  ]
}
```

---

### `POST /api/factories`

Create a factory.

**Request body**

| Field | Type | Rules |
|---|---|---|
| `factory_name` | string | required, max 255 |
| `location` | string | required, max 255 |
| `email` | string | nullable, valid email, max 255 |
| `website` | string | nullable, valid URL, max 255 |

```json
{
  "factory_name": "New Horizon Factory",
  "location": "Cebu City",
  "email": "info@newhorizon.example",
  "website": "https://newhorizon.example"
}
```

**Success — `201 Created`**

```json
{
  "success": true,
  "data": {
    "id": 66,
    "factory_name": "New Horizon Factory",
    "location": "Cebu City",
    "email": "info@newhorizon.example",
    "website": "https://newhorizon.example",
    "employees_count": 0
  }
}
```

---

### `GET /api/factories/{id}`

Fetch a single factory. Returns the same factory object. `404` if not found.

### `PUT/PATCH /api/factories/{id}`

Update a factory. Same body and rules as create. Returns the updated factory object.

### `DELETE /api/factories/{id}`

**Success — `200 OK`**

```json
{
  "success": true,
  "message": "Factory deleted successfully."
}
```

---

## `GET /api/dashboard`

Aggregate metrics for the admin dashboard. Requires an **admin** Bearer token.

**Success — `200 OK`**

```json
{
  "success": true,
  "factories": 61,
  "employees": 1091,
  "logged_events": 31,
  "avg_per_factory": 17.9,
  "top_factories": [
    { "id": 5, "factory_name": "Robel, Cronin and Wiza Factory", "employees_count": 33 },
    { "id": 3, "factory_name": "Hodkiewicz Group Factory", "employees_count": 33 }
  ],
  "recent_activity": [
    {
      "action": "updated",
      "model": "Employee",
      "record_id": 13,
      "user_id": 1,
      "changes": {
        "lastname": { "old": "Cremin", "new": "Cremin-Updated" }
      },
      "time_ago": "2 minutes ago"
    }
  ]
}
```

**Field reference**

| Field | Description |
|---|---|
| `factories` | Total factory count. |
| `employees` | Total employee count. |
| `logged_events` | Number of recorded activity-log events. |
| `avg_per_factory` | Average employees per factory (1 decimal; `0` when there are no factories). |
| `top_factories` | Up to 5 factories with the highest headcount. |
| `recent_activity` | Up to 5 most recent activity events, newest first. |

---

## `GET /api/logs`

Model activity log (created/updated/deleted events for factories and employees), **newest first**.
Requires an **admin** Bearer token.

**Query parameters**

| Param | Type | Description |
|---|---|---|
| `model` | string | Filter by model: `Employee` or `Factory`. |
| `action` | string | Filter by action: `created`, `updated`, or `deleted`. |

**Success — `200 OK`**

```json
{
  "success": true,
  "total": 2,
  "data": [
    {
      "action": "updated",
      "model": "Employee",
      "record_id": 13,
      "user_id": 1,
      "changes": {
        "lastname": { "old": "Cremin", "new": "Cremin-Updated" }
      },
      "user_email": "admin@admin.com",
      "timestamp": "2026-06-06T00:20:47+00:00"
    },
    {
      "action": "created",
      "model": "Factory",
      "record_id": 66,
      "user_id": 1,
      "user_email": "admin@admin.com",
      "timestamp": "2026-06-06T00:18:10+00:00"
    }
  ]
}
```

**Field reference**

| Field | Description |
|---|---|
| `action` | `created`, `updated`, or `deleted`. |
| `model` | `Employee` or `Factory`. |
| `record_id` | Primary key of the affected record. |
| `user_id` | Id of the acting user (`null` if none). |
| `changes` | Present only on `updated`; maps each field to `{ old, new }`. |
| `user_email` | Email resolved from `user_id` (`null` if user missing/deleted). |
| `timestamp` | ISO-8601 time of the event. |

**Example:** `GET /api/logs?model=Employee&action=updated`

---

## Quick Start

```bash
# 1. Log in and capture the token
TOKEN=$(curl -s -X POST http://127.0.0.1:8000/api/auth/admin/login \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"email":"admin@admin.com","password":"password"}' \
  | php -r 'echo json_decode(file_get_contents("php://stdin"),true)["data"]["access_token"];')

# 2. Call a protected endpoint
curl http://127.0.0.1:8000/api/employees \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```
