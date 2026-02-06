# CARES

CARES is a lightweight PHP application for managing accounts, guidance scoring, and admin workflows.

## Prerequisites

- PHP 8.1+
- MySQL 8+

## Getting started

1. Copy the environment template and update the values for your machine:

   ```bash
   cp .env.example .env
   ```

2. Update database settings in `.env`.
3. Run the built-in PHP server from the repository root:

   ```bash
   php -S localhost:8000 -t public
   ```

4. Visit `http://localhost:8000`.

> If you deploy the app under a subfolder, set `BASE_PATH` and `APP_URL` in `.env` to match.

## Configuration

All application configuration values live in `.env` and are loaded at runtime. The application falls back to sensible defaults if the file is missing.

## Development notes

- Session data is stored in PHP sessions.
- Email uses the Brevo (Sendinblue) API; set `BREVO_API_KEY` to enable sending emails.

## Testing

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

## Project structure

```
app/        Application controllers, middleware, and services
core/       Framework utilities (routing, DB, env loader)
public/     Public entrypoint
scripts/    Developer utilities
```
