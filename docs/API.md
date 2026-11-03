# FarmSync API routes

Base path: `/api/v1`. All endpoints return JSON. Authenticated endpoints require
`Authorization: Bearer <token>` issued by the login or registration endpoint.

## Authentication

| Method | Path | Auth | Purpose |
|---|---|---|---|
| POST | `/auth/register` | Public, throttled | Register a Farm Owner and return a token |
| POST | `/auth/login` | Public, throttled | Verify credentials and return a token |
| POST | `/auth/logout` | Sanctum | Revoke the current token |
| GET | `/auth/me` | Sanctum | Return the current user and roles |

Registration requires `name`, `email`, `password`, and `password_confirmation`.
Login requires `email` and `password`. Registration/login return
`{ "data": { "token": "...", "user": { "id", "name", "email", "roles" } } }`.

## Farms

| Method | Path | Purpose |
|---|---|---|
| GET | `/farms` | Paginated list; supports `search` and `per_page` |
| POST | `/farms` | Create a farm owned by the current user |
| GET | `/farms/{farm}` | Read a farm |
| PUT/PATCH | `/farms/{farm}` | Update a farm |
| DELETE | `/farms/{farm}` | Soft-delete a farm |

Farm fields: `name` (required), `location`, `latitude`, `longitude`, and
`total_area_hectares` (required and positive).

## Fields

| Method | Path | Purpose |
|---|---|---|
| GET | `/farms/{farm}/fields` | Paginated list; supports `search`, `status`, and `per_page` |
| POST | `/farms/{farm}/fields` | Add a field to a farm |
| GET | `/farms/{farm}/fields/{field}` | Read a field scoped to its farm |
| PUT/PATCH | `/farms/{farm}/fields/{field}` | Update a field |
| DELETE | `/farms/{farm}/fields/{field}` | Soft-delete a field |

Field fields: `name`, `area_hectares`, `soil_type`, `irrigation_type`, `status`,
`boundary` (an array of at least three `{ "lat": ..., "lng": ... }` points),
`latitude`, and `longitude`. Valid statuses are `Fallow`, `Prepared`, `Planted`,
`Growing`, and `Harvested`.

The API uses Laravel API Resources. Paginated collections return the standard
`data`, `links`, and `meta` keys. Validation failures return HTTP 422 with
`message` and a field-keyed `errors` object; unauthenticated requests return
401, and policy denials return 403.
