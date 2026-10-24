# QRVisits — Status

**Phase:** implemented, verified by automated tests
**Plan:** `.agents/plans/qrvisits-implementation-plan.md`
**Last updated:** 2026-10-24

## Recent fix — "Request a visit" button did nothing

- **Cause:** the pages that *show* the request forms (`/visits/create` and `/walk-ins/create`) reused the form-submission request classes. Those classes validated the empty page request, so the server redirected straight back to the dashboard.
- **Fix:** added `ShowVisitCreateRequest` and `ShowWalkInCreateRequest` (authorization only) and used them for the GET pages. `CreateVisitRequest` / `CreateWalkInRequest` now run only on submit.
- **Also fixed:** demo accounts were seeded with two-factor authentication enabled, which sent the demo logins to a two-factor screen. The seeder is now idempotent and clears two-factor for the demo accounts (`php artisan db:seed` is safe to re-run).
- **Also fixed:** a stale `public/hot` file left behind by a crashed Vite dev server made Inertia try to reach a server-sent-rendering process; removed. If tests ever fail with `__inertia_ssr` errors, delete `public/hot`.
- **Verified in a real browser:** visitor login → dashboard → "Request a visit" → form → submit → pending visit page; admin approvals page; guard walk-in page and gate scan page, with no JavaScript errors.
- **Regression tests added:** the request form pages render for the right roles and are hidden from the wrong ones (+3 tests; 218 total in Feature + Unit).

## Change — expected arrival removed from visitor requests

- The "Expected arrival" field was removed from the visitor's **Request a visit** form at the user's request.
- New visitor requests no longer store an arrival time; the walk-in form still records "Time arrived".
- Existing visits keep their stored arrival time, and the UI now hides the "Expected" line when it is empty (visit detail and live board).
- Visitor requests still require: purpose, person/office to visit, visit date, and contact number.

## Environment notes

- The project now runs on PHP 8.4: `composer.json` requires `^8.4.0` and `.php-version` is set to `8.4`, matching the PHP bundled with Herd.
- `.env` created, app key generated, SQLite database migrated and seeded.
- Added `bacon/bacon-qr-code` for server-side QR SVG generation.
- Frontend built (`npm run build`).

## Completed

- Users can have a role (`visitor`, `guard`, `admin`) and a contact number.
- Visits table with pass code, approval, denial, time-in/time-out and guard audit fields.
- Visitor: request a visit, view status, view/print QR pass, cancel pending, visit history.
- Admin: approvals queue, approve/deny with reason, dashboards, live board, emergency list, reports + CSV, staff accounts.
- Guard: gate scan page (camera with manual fallback), walk-in registration, live board, emergency list.
- Role-aware dashboard and sidebar navigation.
- Demo seeder with admin/guard/visitor accounts and sample visits.

## Verification

| Check | Result |
| --- | --- |
| `php artisan test tests/Feature tests/Unit` | 216 passed, 2 failed (pre-existing ArchTest provider rules) |
| QRVisits feature tests (visits, approvals, scan, walk-ins, reports, staff, dashboards) | all passing |
| `npm run test:types` (vue-tsc) | passed |
| `npx vp lint` | passed |
| `npm run build` | passed |
| `vendor/bin/pint --dirty` | applied |
| `phpstan` | only 2 pre-existing errors (`AppServiceProvider::boot()` return type, Telescope migration) |

## Known issues (not introduced by this work)

1. `tests/Unit/ArchTest.php` has 2 failures from the initial commit: `TelescopeServiceProvider` is not final, and `AppServiceProvider` registers it. Both files are untouched starter-kit code.
2. Browser tests cannot run in this environment: `pest-plugin-browser` reports "Playwright is outdated". Existing browser tests fail for the same reason. `tests/Browser/QrVisitsFlowTest.php` covers the main flows and will run once Playwright versions are aligned.
3. `npm run lint` / `npm run test:lint` scripts use POSIX `NODE_OPTIONS=` syntax and fail on Windows; run `npx vp fmt resources/` and `npx vp lint --fix` instead.

## Remaining / next steps

- Optional: align Playwright and run the browser tests.
- Optional: fix the two pre-existing architecture-test failures in the starter providers.
- UX polish after `/design-app` if the interface is redesigned.

## How to run (Windows)

```powershell
php artisan serve
npm run dev
```

Demo accounts (password `password`):

- `admin@qrvisits.test`
- `guard@qrvisits.test`
- `visitor@qrvisits.test`
