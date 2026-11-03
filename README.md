# FarmSync

FarmSync is a responsive farm operations application. The current implementation
delivers the first vertical slice: Sanctum authentication, role setup, farms,
fields, location pins, and farm/field management screens. Crop planning,
production, inventory, weather, alerts, tasks, and reporting are the next
modules in the requested build sequence.

## Stack

- Laravel 13.39 (the supplied workspace was already on Laravel 13; it was not
  downgraded), PHP 8.3, Sanctum, Spatie Laravel Permission, Eloquent
- React 18, Vite, React Router, TanStack Query, Axios, Tailwind CSS 4,
  React Hook Form, Zod, Leaflet / React Leaflet
- PHPUnit with SQLite in-memory test configuration

## Setup

Requirements: PHP 8.3+, Composer, Node.js/npm, and MySQL or MariaDB.

1. Install dependencies:

   ```powershell
   composer install
   npm install
   ```

2. Create a local environment file only if one does not exist, then set the
   application key and database settings:

   ```powershell
   if (-not (Test-Path .env)) { Copy-Item .env.example .env }
   php artisan key:generate
   ```

   Create a MySQL/MariaDB database (for example, `farmsync`) and set
   `DB_CONNECTION=mysql`, `DB_DATABASE=farmsync`, `DB_USERNAME`, and
   `DB_PASSWORD` in `.env`.

3. Run migrations and load role and demo farm data:

   ```powershell
   php artisan migrate --seed
   ```

4. Start the frontend and Laravel server in separate terminals:

   ```powershell
   npm run dev
   php artisan serve
   ```

5. Open the URL printed by `php artisan serve`. Laravel serves the React SPA
   shell for client routes such as `/login` and `/farms`, so direct links and
   browser refreshes work. Vite serves frontend assets during development and
   the API remains under `/api/v1`.

   Register an account or use the
   local demo account: `owner@farmsync.test` / `password`. Change the demo
   password before using a non-local environment.

Map tiles are loaded from OpenStreetMap. The current milestone does not yet
fetch weather data, so no weather provider key is needed yet.

## Current module: farms and fields

- Create an account and sign in using bearer tokens issued by Sanctum.
- Create, list, search, view, update, and soft-delete farms.
- Create, list, filter, view, update, and soft-delete fields nested under a
  farm.
- Store farm and field coordinates plus an optional field boundary polygon.
- See farm and field location pins on a Leaflet map.
- Restrict farm data to its owner or assigned farm members; farm managers can
  manage fields, while field workers and viewers are read-only for this module.
- Validate form data in both React/Zod and Laravel Form Requests.
- Paginate list APIs (`per_page` is capped at 100); list responses include
  Laravel pagination links and metadata.

### Demo seed data

`php artisan db:seed` creates the following local-only account and sample farm:

- Email: `owner@farmsync.test`
- Password: `password`
- Farm: Green Valley Farm, with North Field and South Field

## API

The current route list, authentication requirements, and response shapes are in
[docs/API.md](./docs/API.md). JSON validation errors use Laravel's standard
`message` and `errors` object. Farm and field API resources are wrapped in
`data`; paginated lists also include `links` and `meta`.

## Database design (target ERD)

The diagram below is the intended core schema for the full FarmSync system.
Only `users`, role/token support, `farms`, `farm_user`, and `fields` are
implemented in this first module; other entities are listed to make the planned
relationships explicit and will be added with their modules.

```text
users ──< farms (owner_id)
users >──< farms (farm_user: farm_id, user_id, role)
farms ──< fields
seasons ──< planting_plans >── crops
fields ──< planting_plans
fields ──< activities ──< activity_inputs >── inventory_items
activities ──< pest_reports
activities ──< harvests ──< harvest_audits
farms ──< tasks >── users (assignee_id)
farms ──< weather_readings
farms ──< weather_forecasts
farms ──< alert_rules ──< alerts
users ──< notifications
suppliers ──< inventory_items
inventory_items ──< stock_movements
fields ──< expenses ──< expense_audits
users ──< activities (worker_id)
```

In the implemented farm/field schema, owner/farm/field foreign keys are indexed,
`fields` is indexed by `(farm_id, status)` and `(farm_id, name)`, and farms and
fields use soft deletes. The target schema will add compound indexes for
farm/season/date lookups and soft deletes for operational records that should be
recoverable. Harvest and expense audit rows will reference the edited record,
actor, action, and before/after values.

## Tests

Run the PHPUnit suite (the repository's `phpunit.xml` configures SQLite
in-memory):

```powershell
php artisan test
```

The current feature tests cover authentication and token revocation, farm
authorization and validation, field status and boundary validation, nested
resource isolation, and role-based field access.

## Build

```powershell
npm run build
```
