# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

**SIP-MAGANG** — internship lifecycle management system for Pemerintah Kota Surabaya (agencies ↔ universities ↔ students). UI text, labels, comments and most docs are in Bahasa Indonesia; keep new user-facing strings in Indonesian.

## Tech Stack

- Laravel 13 (`laravel/framework ^13.8`), PHP ^8.3, Breeze auth, Sanctum (API tokens)
- PostgreSQL in dev/prod (`DB_CONNECTION=pgsql`); **tests run on SQLite `:memory:`** (see `phpunit.xml`)
- Blade + Tailwind CSS 3 + Alpine.js, bundled by Vite
- `barryvdh/laravel-dompdf` (letters/certificates), `simplesoftwareio/simple-qrcode` (verification QR codes)
- Tests: PHPUnit 12 (no Pest installed)

## Commands

```bash
composer setup                     # install, .env, key, migrate, npm build
composer dev                       # serve + queue:listen + pail logs + vite (concurrently)
php artisan migrate:fresh --seed   # DatabaseSeeder → DemoE2ESeeder: full 6-role demo data
php artisan storage:link           # required for logos/uploads (see learnings LRN-001/LRN-013 for Windows junction issues)

php artisan test                                                   # full suite
php artisan test tests/Feature/ApplicationStatusArchitectureTest.php
php artisan test --filter=test_review_status_enum_values           # single test method
php -l path/to/File.php                                            # syntax check
vendor/bin/pint path/to/File.php                                   # formatter (Laravel Pint)
php artisan optimize:clear && php artisan view:clear               # clear caches after view/route/config changes

npm run test:hermes    # Puppeteer multi-role E2E scripts in scripts/hermes_*.mjs (needs running app at 127.0.0.1:8000)
```

Demo accounts (all password `password`): `superadmin@surabaya.go.id`, `admin.kominfo@surabaya.go.id`, `mentor.kominfo@surabaya.go.id`, `admin@unesa.ac.id`, `dosen.unesa@unesa.ac.id`, `mahasiswa@unesa.ac.id`.

## Architecture

### Roles & authorization
- `users.role` is a plain string. Roles: `super_admin`, `admin` (Admin Dinas), `mentor` / `pembimbing` (field mentor, aliases), `dosen` / `academic_advisor` (university lecturer/DPL, aliases), `universitas` (university admin), `mahasiswa` (student).
- `App\Http\Middleware\CheckRole` (alias `role`, registered in `bootstrap/app.php`) handles aliases and a **Super Admin bypass**: a user with role `super_admin`, *or* `admin` with `agency_profile_id = null`, passes any `role:admin` / `role:super_admin` gate. Agency admins are scoped by `agency_profile_id`; university users by `university_id`.
- Impersonation (Quick Role Switcher, `Admin\ImpersonationController`) stores `impersonator_id` in session; `CheckRole` redirects impersonated users to their own dashboard instead of 403.
- `routes/web.php` groups routes per role with URL/name prefixes: `student.*`, `admin.*`, `mentor.*`, `lecturer.*`, `university.*`. Controllers live in matching namespaces under `app/Http/Controllers/{Student,Admin,Mentor,Lecturer,University}` (plus a legacy `Pembimbing/` namespace). `routes/api.php` only exposes Sanctum login/me/logout.

### Domain model & lifecycle
- `Application` (student's submission to a `Unit`, which belongs to an `AgencyProfile`) → on acceptance gets one `Placement` linking mentor (`mentor`/`pembimbing` relations) and DPL (`academicAdvisor`/`dosen` relations). `Placement` has `logbooks`, `finalreport`, `evaluation`, and certificate fields (`certificate_hash`).
- `Application.status` is cast to `App\Enums\ApplicationStatus`: `pending → verified → accepted → active → completed`, plus `rejected`, `resigned`. The enum is the single source of truth for labels (`label()`), badge colors (`badgeColor()`, `dotColor()`) and permissions (`canLogbook()` = only `ACTIVE`). Logbook/final-report reviews use `App\Enums\ReviewStatus` (`pending/approved/rejected/revision`).
- Status transitions that are automatic:
  - `accepted → active`: `app:sync-internship-status` command, scheduled daily in `routes/console.php`, when the start date arrives.
  - `active → completed`: `Placement::syncCompletionStatus()` once the final report is approved and evaluations are complete.
- Grading: final score = 40% mentor (field) + 60% DPL (academic), stored on `Evaluation`; university-level evaluation policy lives on `universities`.
- Public, unauthenticated verification routes (defined inline in `routes/web.php`): `/verify-letter/{token}` (by `applications.letter_token`) and `/verify-certificate/{token}` (by `placements.certificate_hash`), both with numeric-ID fallback.
- `App\Services\NotificationService` + `SystemNotification`/`AuditLog` models handle in-app notifications and audit trails.

### Gotchas
- Because status may arrive as an enum or a raw string (legacy code, raw queries), compare via `$status instanceof \BackedEnum ? $status->value : $status`. Never call `strtolower()`/string functions directly on an enum in Blade or services (LRN-022).
- Migrations must run on both PostgreSQL and SQLite (tests). Postgres-only DDL (e.g. dropping `applications_status_check`) is wrapped in try/catch or a driver check — follow that pattern.
- Avoid passing full Eloquent models into `@json`/`json_encode` in Blade — caused memory exhaustion (LRN-020); map to arrays first.
- `scripts/*.php` are ad-hoc maintenance/debug scripts, not part of the app.

## Project conventions (from AGENTS.md / .antigravity/)

- Multi-agent setup: Antigravity (Gemini) is architect/QA, Claude Code is the multi-file implementer, Aider does small diffs. See `AGENTS.md`, `AGENTS_WORKFLOW.md`, `.antigravity/agent_rules.md`, and modular rules in `.antigravity/rules/{architecture,database,media,quality}.md`.
- **Read `.antigravity/memory/learnings.md` before non-trivial changes** — it records past bugs with root causes and prevention rules. After fixing a notable bug, append an entry using its `[LRN-XXX]` template (Problem, Root Cause, Fix Applied, Prevention Rule).
- Verify real column names against `database/migrations/` before writing queries or model changes; no raw SQL without parameter binding; use `DB::transaction` for multi-table writes.
- Thin controllers: when a method exceeds ~15 lines or a controller ~100 lines, move logic into `app/Services/`.
- Make surgical, backward-compatible edits (keep accessors/aliases that older views rely on, e.g. `lifecycle_status`, `rejection_reason`/`rejection_note`).
- Done means `php -l` on changed files and `php artisan test` both exit 0; fix failures before reporting completion.
- Logos/media: reference via `asset('storage/logos/...')` with `object-contain` to avoid distortion (see `.antigravity/rules/media.md`).
