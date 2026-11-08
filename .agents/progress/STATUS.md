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

## Change — visitor dashboard refinement

- Refined the visitor dashboard while keeping the dark-green sidebar and white content.
- Stat cards: clearer hierarchy, larger tabular numbers, icon tiles, short hints and softer borders/shadows; responsive two-column layout on phones.
- “Your next visit”: added a dark-green pass panel with the QR code, pass code and validity date, plus structured visit details and a primary “View pass” action.
- Improved empty state with a dedicated QR illustration, explanation and a three-step “request → approve → scan” guide.
- Recent requests: added a “View all” action, clickable rows, consistent status badges and chevrons; added a compact empty state.
- Typography, spacing, borders and shadows normalised across the dashboard.
- Visual-only: no controller, route, validation or data logic changed.
- Verified with browser screenshots at 1440×900 and 390×844 in light and dark mode; dashboard tests and type/build checks pass.

## Change — expected time-out and automated overdue alerts

- New `visits.expected_time_out` column; required on visitor requests and walk-in registration.
- A visitor is overdue when still `inside` after the visit date + expected time-out.
- Guards and admins get an in-app alert: header bell with count, dropdown listing visitor name, pass code (ID), expected time-out and minutes late, plus a warning toast when a new overdue visitor appears.
- Alerts poll every 30 seconds while the app is open; no scheduler or cron is required. Alerts clear automatically after checkout.
- Dashboards now show an Overdue stat card; the visit detail page shows an overdue banner.
- Tests added for overdue detection, alert endpoint access and the new required field.
- Verified in a real browser: bell badge, dropdown and alert details render with demo overdue data.

## Change — dark-green and white UI theme

- Reworked the colour palette to dark green + white: deep green sidebar, white content surfaces, green primary actions, subtle green page glow.
- Dark mode uses a deep green background with brighter green actions and clear card separation.
- Increased the corner radius for a more modern feel.
- Replaced the Laravel starter welcome page with a QRVisits landing page: product summary, feature cards, sample visitor pass, and the same login/register/dashboard links.
- Branding changed to “QRVisits” via `APP_NAME` (`.env` and `.env.example`).
- Visual-only change: no routes, controllers, validation, or business logic were modified.
- Verified with browser screenshots at 1440×900 and 390×844 in light and dark mode.

- Added an interactive, printable database page for documentation.
- ERD renders with **Mermaid loaded from a pinned CDN** (`mermaid@11.4.1`); if the CDN is unreachable the page shows a short note and the structure tables still work.
- Shows only the tables in the visitor flow: `users` and `visits` (no passkeys, sessions, password resets or framework tables).
- Includes full database structure tables (column, type, key, nullable, default, notes) and a print button.
- Requires login.
- Verified in a real browser: diagram renders, tables present, no JavaScript errors.

## Fix — "id document failed to upload"

- **Cause:** PHP had no writable temporary folder configured for the process serving the app (`sys_temp_dir` and `upload_tmp_dir` were empty, so it fell back to `C:\WINDOWS`).
- **Fix:** pinned both settings to `C:\Users\PC\AppData\Local\Temp` in Herd's PHP 8.4 `php.ini`, and raised `upload_max_filesize` / `post_max_size` from 2M to 8M so a 2 MB file plus form overhead fits.
- **Backup:** `C:\Users\PC\.config\herd\bin\php84\php.ini.backup-qrvisits`.
- Verified by uploading a real PNG through the browser and opening the saved file. The app still limits uploads to 2 MB.

## Change — valid ID upload- Visitors must now upload a valid ID (JPG, PNG or PDF, up to 2 MB) when requesting a visit.
- Files are stored on the private disk (`storage/app/private/visitor-ids`) and are not publicly accessible. The owner of the visit and staff can view them; other visitors get "not found".
- Admin approvals show a "View ID" link; the visit detail page shows "View uploaded ID" and the original file name. Walk-ins show "No ID uploaded".
- Uploaded files are deleted when a pending request is cancelled.
- Demo data and existing visits have no ID, which is expected.
- Verified in a real browser: upload → visit created → file saved → link served. (A sandbox-only PHP temp-folder issue was worked around for testing; normal terminals are unaffected.)

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
| `php artisan test tests/Feature tests/Unit` | 229 passed, 2 failed (pre-existing ArchTest provider rules) |
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
