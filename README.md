# CARES

CARES is a lightweight PHP application for managing accounts, admission scoring, and admin workflows.

## Features

- Role-based access for administrators and admission personnel
- Student records and admission status tracking
- Exam parts, weights, and scoring storage
- Audit logs for key actions
- Email verification and password reset support

## Requirements

- Windows, macOS, or Linux
- PHP 8.1+ with the following extensions enabled:
  - `pdo`
  - `pdo_mysql`
  - `mbstring`
  - `openssl`
  - `curl`
  - `json`
- MySQL 8+ or MariaDB 10.4+
- Composer (optional; not required to run the app as currently structured)

## Project Structure

```
app/        Application controllers, middleware, and services
core/       Framework utilities (routing, DB, env loader)
database/   SQL schema and seed data
public/     Public web entrypoint
scripts/    Developer utilities/tests
```

## Setup (Local)

1. Clone or copy the project to your web root.
2. Copy the environment template and update values for your machine:

   ```bash
   cp .env.example .env
   ```

3. Update `.env` with your database and base URL settings:

   - `DB_HOST`
   - `DB_PORT`
   - `DB_DATABASE`
   - `DB_USERNAME`
   - `DB_PASSWORD`
   - `APP_URL`
   - `BASE_PATH` (if hosted in a subfolder)

4. Create the database in MySQL.
5. Import the schema, then the seed data:

   ```sql
   -- schema
   database/schema.sql

   -- seed data
   database/seed.sql
   ```

6. Start the PHP built-in server from the repo root:

   ```bash
   php -S localhost:8000 -t public
   ```

7. Visit `http://localhost:8000`.

## Default Accounts

- Administrator (created by schema import)
  - Email: `admin@cares.local`
  - Password: `123456789`
- Admission users (created by seed import)
  - See `database/seed.sql` for sample accounts

If you want to change credentials, update the inserts in `database/schema.sql` and `database/seed.sql`.

## Database Files

- Schema: `database/schema.sql`
- Seed data: `database/seed.sql`

Import the schema first, then the seed data. The schema inserts the default admin account, while the seed file inserts admission users.

## Configuration

All application configuration values live in `.env` and are loaded at runtime. The application falls back to sensible defaults if the file is missing.

## Running Tests

Run the smoke tests for account and student management:

```bash
php scripts/smoke_test.php
```

Run the authentication tests:

```bash
php scripts/auth_test.php
```

Run all tests:

```bash
php scripts/run_tests.php
```

## Common Tasks

- Reset database: re-import `database/schema.sql` then `database/seed.sql`
- Change logo or branding: check `public/assets/` and view files in `app/Views/`
- Update weights or exam parts: edit seed data or manage via the app UI

## Deployment Notes

- Set `APP_URL` and `BASE_PATH` correctly for the deployed location.
- Ensure the web server points to the `public/` directory as the document root.
- Configure PHP sessions and file permissions according to your server.

## Troubleshooting

- Blank page or 500 errors: check PHP error logs and ensure required extensions are enabled.
- Database connection issues: verify `.env` values and MySQL credentials.
- Emails not sending: set `BREVO_API_KEY` in `.env`.
