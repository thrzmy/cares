# CARES

CARES is a lightweight PHP application for admissions account management, student scoring, course recommendation and administrative workflows.

## Features

- Role-based access (`administrator`, `admission`)
- Admission user registration with email verification
- Admin account approval/rejection flow
- Student profile and exam score management
- Course Recommendation
- Course/exam-weight matrix management
- Password reset and forced password change support
- Audit logs for major actions

## Tech Stack

- PHP 8.1+
- MySQL 8+ / MariaDB 10.4+
- Server-rendered PHP views (no required frontend build step)

## Project Structure

```text
app/        Controllers, middleware, services, and views
core/       Framework utilities (router, DB, env, helpers)
database/   SQL schema and seed data
public/     Web entrypoint (document root)
scripts/    Test runner + module test suites
```

## Requirements

- PHP 8.1+ with extensions:
  - `pdo`
  - `pdo_mysql`
  - `mbstring`
  - `openssl`
  - `curl`
  - `json`
- MySQL 8+ or MariaDB 10.4+
- XAMPP/WAMP/LAMP (or any PHP-capable web server)

## Local Setup (Recommended)

1. Clone/copy the project to your web root (example: `C:\xampp\htdocs\cares`).
2. Copy env template:

   PowerShell:
   ```powershell
   Copy-Item .env.example .env
   ```

   Bash:
   ```bash
   cp .env.example .env
   ```

3. Update `.env` values for your environment (see [Environment Variables](#environment-variables)).
4. Create database (example: `cares`).
5. Seed the database:

   1. Import `database/schema.sql`
   2. Run setup seed (courses, exam parts, weights):

   ```bash
   php scripts/seed.php setup --fresh
   ```

   3. Run sample/demo seed data:

   ```bash
   php scripts/seed.php sample
   ```

6. Start app from repo root:

   ```bash
   php -S localhost:8000 -t public
   ```

7. Open:

   `http://localhost:8000`

If hosted in a subfolder (example `http://localhost/cares/public`), set `BASE_PATH` properly in `.env`.

## Environment Variables

All config values are loaded from `.env` at runtime.

```env
APP_URL="http://localhost:8000"
APP_NAME="CAReS"
BASE_PATH=""
APP_TIMEZONE="Asia/Manila"

DB_HOST="127.0.0.1"
DB_NAME="cares"
DB_USER="root"
DB_PASS=""
DB_CHARSET="utf8mb4"

APP_DEBUG="true"

BREVO_API_KEY=""
MAIL_FROM_EMAIL="no-reply@cares.local"
MAIL_FROM_NAME="CAReS"
EMAIL_VERIFICATION_TTL_MINUTES="15"
EMAIL_VERIFICATION_RESEND_SECONDS="60"
```

### Variable Notes

- `APP_URL`: public base URL of the app.
- `BASE_PATH`: leave empty if app is served at domain root; set to subpath if needed (example: `/cares`).
- `APP_DEBUG`: use `false` in production.
- `MAIL_FROM_EMAIL`: must be a sender verified in Brevo for real email sending.

## Default Accounts

Created by schema + sample seed:

- Administrator
  - Email: `admin@cares.local`
  - Password: `123456789`

You can update these in `database/schema.sql` and `database/seed.sql` (sample data source) before seeding.

## Seeder (Simple)

Main editable setup file:

- `database/setup.json`

Commands:

- `php scripts/seed.php setup --fresh` -> initial setup only (truncates and reloads `courses`, `exam_parts`, `weights`)
- `php scripts/seed.php sample` -> load sample/demo data (`users`, `students`, scores, logs, etc.)
- `php scripts/seed.php all --fresh` -> run both setup + sample data

Notes:

- `setup.json` is client-friendly and uses `course_code` + exam part names (no DB IDs).
- `database/seed.sql` is currently used as the sample data source (legacy split source).
- If the system already has data, use `php scripts/seed.php setup` (without `--fresh`) to safely update weights/setup data.
- `--fresh` is recommended only during initial setup or full database reset.

### Editing `database/setup.json` (Client Guide)

To add a new course, update 2 places in `database/setup.json`:

1. Add the course in `courses`
2. Add the matching weights in `weights`

Example (add `BSBA`):

```json
{
  "course_code": "BSBA",
  "course_name": "B.S. in Business Administration"
}
```

```json
{
  "course_code": "BSBA",
  "weights": {
    "English": 25,
    "Filipino": 10,
    "Literature": 10,
    "Math": 25,
    "Science": 10,
    "Studies": 10,
    "Humanities": 10
  }
}
```

Rules:

- `course_code` in `weights` must exactly match the `course_code` in `courses`
- Weight keys must match the names in `exam_parts` exactly
- Total weights per course should equal `100`

After editing, run:

- `php scripts/seed.php setup` (recommended if the system already has data)
- `php scripts/seed.php setup --fresh` (initial setup / full reset only)

## Email Setup (Brevo) - Client Handoff

Use this section when onboarding a client domain/email.

1. Create client Brevo account: `https://www.brevo.com/`
2. Generate API key in Brevo (`SMTP & API` -> `API Keys`).
3. Update `.env`:

   ```env
   BREVO_API_KEY="your_brevo_api_key_here"
   MAIL_FROM_EMAIL="verified_sender@clientdomain.com" #registered email if free version
   MAIL_FROM_NAME="CAReS"
   ```

4. Restart PHP/web server.
5. Test registration email verification and password reset.

### Important

- `MAIL_FROM_EMAIL` must match a Brevo-verified sender.
- Local/demo addresses like `@cares.local` are not valid real senders.
- If `BREVO_API_KEY` is empty/invalid, app uses dev fallback and shows verification code in UI flash messages.

## Running Tests

Main runner:

- `php scripts/run_tests.php`

Test tree:

- `scripts/tests/accounts/account_management_test.php`
- `scripts/tests/auth/auth_test.php`
- `scripts/tests/services/password_service_test.php`
- `scripts/tests/services/token_service_test.php`
- `scripts/tests/services/email_verification_service_test.php`
- `scripts/tests/services/mailer_test.php`
- `scripts/tests/services/weights_service_test.php`
- `scripts/tests/services/scores_recommendation_test.php`
- `scripts/tests/services/logger_test.php`
- `scripts/tests/modules/reports_test.php`
- `scripts/tests/modules/profile_password_test.php`

Run an individual suite directly, for example:

- `php scripts/tests/auth/auth_test.php`

Notes:

- `mailer_test.php` skips outbound send checks when `BREVO_API_KEY` is set to avoid external side effects.

## Deployment Checklist

1. Set production `.env` values:
   - `APP_URL`
   - `BASE_PATH` (if applicable)
   - `APP_DEBUG="false"`
   - DB credentials
   - Brevo mail settings
2. Point web server document root to `public/`.
3. Use HTTPS in production.
4. Ensure PHP session and file permissions are correctly configured.
5. Re-test login, registration, verify-email, and password reset flows.

## Troubleshooting

- Blank page / 500 errors:
  - enable PHP error logs and check web server logs.
  - verify required PHP extensions are enabled.
- Database connection issues:
  - confirm `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` in `.env`.
- Route/path issues under subfolder:
  - set correct `BASE_PATH` (example: `/cares`).
- Email not sending:
  - verify `BREVO_API_KEY` is valid.
  - ensure `MAIL_FROM_EMAIL` is verified in Brevo.

## Common Maintenance Tasks

- Reset DB: re-import `database/schema.sql`, then run `php scripts/seed.php all --fresh`.
- Change setup config seeds: edit `database/setup.json`.
- Change sample/demo seeds: edit `database/seed.sql`, then run `php scripts/seed.php sample`.
- Review auth/account flow: `app/Controllers/AuthController.php`, `app/Controllers/AccountsController.php`.
