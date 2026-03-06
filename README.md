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
5. Seed the database (phpMyAdmin / SQL import):

   1. Import `database/schema.sql`
   2. Import `database/setup_seed.sql` (courses, exam parts, weights)
   3. Optional for local/demo only: import `database/seed.sql` (sample users/students/scores/logs)

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
  - Email: `cares.cct@gmail.com`
  - Password: `123456789`

You can update these in `database/schema.sql` and `database/seed.sql` (sample data source) before seeding.

## Setup Data (SQL / phpMyAdmin)

Use these SQL files in phpMyAdmin:

- `database/schema.sql` -> creates tables + default admin account
- `database/setup_seed.sql` -> inserts/updates courses, exam parts, and weights
- `database/seed.sql` -> optional sample/demo data (users, students, scores, logs)

### Add a New Course (SQL)

1. Insert the course in `courses`
2. Insert related weights in `weights` (one row per exam part)

Example (`BSBA`):

```sql
INSERT INTO courses (course_code, course_name, is_deleted)
VALUES ('BSBA', 'B.S. in Business Administration', 0);
```

Then add weights (example assumes exam parts already exist and uses names, not hardcoded IDs):

```sql
INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 25.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'English'
WHERE c.course_code = 'BSBA';

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 10.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Filipino'
WHERE c.course_code = 'BSBA';

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 10.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Literature'
WHERE c.course_code = 'BSBA';

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 25.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Math'
WHERE c.course_code = 'BSBA';

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 10.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Science'
WHERE c.course_code = 'BSBA';

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 10.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Studies'
WHERE c.course_code = 'BSBA';

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 10.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Humanities'
WHERE c.course_code = 'BSBA';
```

Rules:

- `course_code` must be unique
- Weight rows must match existing `exam_parts.name`
- Total weights per course should equal `100`

### Add / Update an Exam Part (SQL)

Add a new exam part:

```sql
INSERT INTO exam_parts (name, max_score, is_deleted)
VALUES ('Logic', 30.00, 0);
```

Update max score of an existing exam part:

```sql
UPDATE exam_parts
SET max_score = 40.00, is_deleted = 0, deleted_at = NULL
WHERE name = 'Math';
```

Important:

- If you add a new exam part, you should also add corresponding `weights` rows for every course.

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

- Reset DB: re-import `database/schema.sql`, then `database/setup_seed.sql` (and optionally `database/seed.sql` for demo data).
- Change setup data: edit/import `database/setup_seed.sql` or run SQL manually in phpMyAdmin.
- Change sample/demo seeds: edit `database/seed.sql` before importing.
- Review auth/account flow: `app/Controllers/AuthController.php`, `app/Controllers/AccountsController.php`.
