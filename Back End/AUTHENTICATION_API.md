# Authentication API Documentation

## Overview

This application uses Laravel Sanctum for API authentication.
Only admin users can log in and access protected endpoints.

## Admin login

### Endpoint

- `POST /api/auth/admin/login`

### Request Headers

- `Content-Type: application/json`
- `Accept: application/json`

### Request Body

```json
{
  "email": "admin@admin.com",
  "password": "password"
}
```

### Success Response

```json
{
  "access_token": "<token>",
  "token_type": "Bearer",
  "expires_at": "2026-06-06 15:30:00"
}
```

### Failure Response

```json
{
  "message": "Invalid credentials or unauthorized."
}
```

## Use the token

Include the token on every protected request:

```http
Authorization: Bearer <token>
```

## Logout

### Endpoint

- `POST /api/auth/logout`

### Headers

- `Authorization: Bearer <token>`
- `Accept: application/json`

### Success Response

```json
{
  "message": "Logged out successfully."
}
```

## Protected routes

All protected routes require a valid Sanctum bearer token and admin access.
The following routes are protected by `auth:sanctum` and the `admin` middleware:

- `GET /api/dashboard`
- `GET /api/logs`
- `GET /api/factories`
- `POST /api/factories`
- `GET /api/factories/{factory}`
- `PUT|PATCH /api/factories/{factory}`
- `DELETE /api/factories/{factory}`
- `GET /api/employees`
- `POST /api/employees`
- `GET /api/employees/{employee}`
- `PUT|PATCH /api/employees/{employee}`
- `DELETE /api/employees/{employee}`

## Admin seeder credentials

The seeded admin user is:

- Email: `admin@admin.com`
- Password: `password`

If the account already exists, run the seeder again to ensure it has the `administrator` role.

## Notes

- API key authentication has been removed.
- All requests now rely only on Laravel Sanctum personal access tokens.
