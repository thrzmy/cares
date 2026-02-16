# Changelog

All notable changes to this project will be documented in this file.

## Unreleased

### Project Finalization

- Added `.editorconfig` for consistent cross-editor formatting defaults.
- Added GitHub Actions CI workflow (`.github/workflows/ci.yml`) to run:
  - MySQL-backed DB import (`database/schema.sql`, `database/seed.sql`)
  - PHP syntax lint
  - Automated test suite (`php scripts/run_tests.php`)
- Finalized test runner configuration by aligning `scripts/run_tests.php` with existing test files.
- Added release operations checklist (`RELEASE_CHECKLIST.md`) for deployment and handoff readiness.
- Added repository operations map (`REPO_MAP.md`) for quick orientation.
- Updated README with quality-check commands, CI coverage, and maintenance links.
