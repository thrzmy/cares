# Repo Map

Short operational map of the CARES repository.

## Stack

- Language: PHP 8.1+
- App style: server-rendered MVC-like structure (custom lightweight framework in `core/`)
- Database: MySQL/MariaDB
- Frontend: Bootstrap + custom CSS

## Entrypoints

- Web entrypoint: `public/index.php`
- Bootstrap/config loading: `core/bootstrap.php`
- Route registration: `public/index.php`

## Core Directories

- `app/Controllers`: request handlers and page actions
- `app/Services`: business/data logic helpers
- `app/Views`: page templates
- `app/Middleware`: auth and role guards
- `core/`: router, DB, env loader, helpers
- `database/`: schema + seed SQL
- `scripts/`: local automated test scripts

## Main Local Commands

- Run app:
  - `php -S localhost:8000 -t public`
- Run tests:
  - `php scripts/run_tests.php`
- Syntax lint:
  - `find . -name "*.php" -print0 | xargs -0 -n1 php -l`

## CI

- Workflow: `.github/workflows/ci.yml`
- Steps:
  1. Setup PHP
  2. Start MySQL service
  3. Import `database/schema.sql` and `database/seed.sql`
  4. Run PHP lint
  5. Run `php scripts/run_tests.php`
