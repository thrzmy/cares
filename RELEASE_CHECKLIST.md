# CARES Release Checklist

Use this before final handoff or production deployment.

## 1. Environment

- [ ] `.env` is configured for target server.
- [ ] `APP_URL` is correct.
- [ ] `BASE_PATH` is correct (`""` if root).
- [ ] `APP_DEBUG="false"` for production.
- [ ] Production DB credentials are set (`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`).

## 2. Database

- [ ] Database backup taken before release.
- [ ] Schema imported successfully (`database/schema.sql`).
- [ ] Seed imported if needed (`database/seed.sql`).
- [ ] Default admin credentials replaced.

## 3. Email

- [ ] `BREVO_API_KEY` is set in `.env`.
- [ ] `MAIL_FROM_EMAIL` is a verified sender in Brevo.
- [ ] `MAIL_FROM_NAME` is set to client-facing name.
- [ ] Registration verification email arrives successfully.
- [ ] Forgot-password email arrives successfully.

## 4. Core Functional Testing

- [ ] Login/logout works for administrator.
- [ ] Login/logout works for admission.
- [ ] Register -> verify-email -> admin approval flow works.
- [ ] Account management actions work (create/edit/verify/reject/reset password).
- [ ] Student management works (create/edit/search/filter).
- [ ] Score encode/edit/view flows work.
- [ ] Results/recommendations render correctly.
- [ ] Reports page loads and filters correctly.
- [ ] Activity logs render, filter, and search correctly (admin + admission).
- [ ] My Profile works for administrator.
- [ ] My Profile works for admission.

## 5. Automated Tests

- [ ] `php scripts/auth_test.php` passes.
- [ ] `php scripts/acct_mang_test.php` passes.
- [ ] `php scripts/run_tests.php` passes.

## 6. Security and Access

- [ ] CSRF-protected forms work normally.
- [ ] Direct URL access to protected pages is blocked when not logged in.
- [ ] Role restrictions are enforced (`administrator` vs `admission`).
- [ ] Force-password-change behavior works.
- [ ] Account lockout behavior works after failed logins.

## 7. UI/UX Verification

- [ ] Pages checked on desktop.
- [ ] Pages checked on mobile.
- [ ] Navigation links are correct per role.
- [ ] Empty states and error messages are understandable.

## 8. Deployment

- [ ] Server document root points to `public/`.
- [ ] Required PHP extensions are enabled (`pdo`, `pdo_mysql`, `mbstring`, `openssl`, `curl`, `json`).
- [ ] HTTPS is enabled.
- [ ] PHP/web server restarted after env changes.

## 9. Handoff

- [ ] Final README reviewed with client.
- [ ] Client receives admin credentials and first-login instructions.
- [ ] Client receives email setup instructions (Brevo sender/API key).
- [ ] Rollback plan documented (restore DB and files).
