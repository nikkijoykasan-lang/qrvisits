# QRVisits — Implementation Plan

Source of truth for requirements: `.agents/context/PROJECT.md`, `USERS.md`, `FEATURES.md`, `DECISIONS.md`.

## 1. Requirements summary

- Visitor accounts with pre-registration for a specific date.
- Admin approval issues a QR pass; denial carries a reason.
- Guard scans the pass at the gate: first scan time-in, second scan time-out.
- Walk-in visitors registered by the guard, approved by the admin.
- Live "inside now" board and printable emergency list.
- Visit history, simple daily reports, CSV export.
- Admin manages guard/admin accounts.
- Pass validity is limited to its visit date.

## 2. User flows

### Visitor
1. Register / log in.
2. Submit request (purpose, host, date, expected arrival, contact number).
3. See status: pending → approved / denied.
4. Open the pass and show the QR at the gate (or print it).
5. Cancel a pending request if plans change.

### Admin
1. Open dashboard, see pending approvals and who is inside.
2. Approve a request → pass code generated → visitor can view QR.
3. Deny a request with a reason.
4. Review the live board, print the emergency list.
5. Filter history and export a CSV report.
6. Create guard/admin accounts.

### Guard
1. Open the scan screen at the gate.
2. Scan the QR (or type the code).
3. System reports "checked in" or "checked out", or a clear refusal.
4. Register a walk-in when needed; admin approves it.
5. Watch the live board.

## 3. Data changes

### `users` (alter)
- `role` string, default `visitor`, indexed.
- `contact_number` string, nullable.

### `visits` (new)
- `id` uuid primary key.
- `user_id` uuid nullable, FK users, null on delete (pre-registered visitor; null for walk-ins).
- `visitor_name`, `visitor_contact`, `purpose`, `host_name` strings.
- `visit_date` date, indexed.
- `expected_arrival` time nullable.
- `status` string, default `pending`, indexed.
- `pass_code` string(12) nullable, unique.
- `denial_reason` text nullable.
- `is_walk_in` boolean, default false.
- `approved_at` timestamp nullable, `approved_by` uuid nullable FK users.
- `time_in`, `time_out` timestamps nullable.
- `checked_in_by`, `checked_out_by` uuid nullable FK users.
- timestamps.

## 4. Backend tasks

- [x] Migration: add role and contact number to users.
- [x] Migration: create visits table.
- [x] Enums: `Role`, `VisitStatus`, `ScanOutcome`.
- [x] Model: `Visit` with casts, relations, scopes, pass validity helper.
- [x] Model: `User` role cast, relations, role helpers.
- [x] Actions: `CreateVisit`, `ApproveVisit`, `DenyVisit`, `CancelVisit`, `CreateWalkIn`, `CheckInVisit`, `CheckOutVisit`, `RecordPassScan`, `CreateStaffAccount`.
- [x] Service: QR code SVG generator using `bacon/bacon-qr-code`.
- [x] Form requests for every endpoint (authorization + validation).
- [x] Controllers: dashboard, visits, approvals, denials, walk-ins, gate scan, live board, emergency list, reports, report export, staff accounts.
- [x] Routes with role-appropriate middleware.
- [x] Factory/seeder: `VisitFactory`, demo admin/guard/visitor accounts and sample visits.

## 5. Frontend tasks

- [x] Shared types for visits and roles.
- [x] Status badge, stat card, empty state components.
- [x] Role-aware sidebar navigation.
- [x] Visitor dashboard, visit list, request form, pass page.
- [x] Admin dashboard, approvals queue with deny dialog.
- [x] Guard dashboard, scan screen, live board.
- [x] Walk-in form.
- [x] Emergency list printable page.
- [x] Reports page with filters and CSV link.
- [x] Staff accounts list/create.

## 6. Validation and error handling

- Visit date must be today or later when requesting.
- Contact number required, 7–20 characters, digits/spaces/+/-.
- Approval/denial only from `pending`; cannot approve twice.
- Scan refusals return a clear reason: unknown code, not approved yet, denied, wrong date, already completed.
- Guard cannot check out a visitor who is not inside.
- Visitors cannot view another visitor's pass (hidden as not found).
- Walk-in requires name, contact, purpose and host.

## 7. Testing strategy

Feature tests:
- Visitor creates a request; a past date is rejected.
- Admin approves → pass code issued; visitor sees the pass.
- Non-admin cannot approve; visitor cannot approve.
- Visitor cannot see another visitor's visit.
- Guard check-in records time-in, second check-out records time-out.
- Wrong-date and pending passes are refused at the gate.
- Walk-in created by guard is pending until approved.
- Report access limited to admin; guard blocked.

Unit tests:
- `Visit` validity helper.
- `RecordPassScan` outcomes.

## 8. Dependency-ordered checklist

| # | Task | Blocked by |
| --- | --- | --- |
| 1 | Users role/contact migration | — |
| 2 | Visits migration | 1 |
| 3 | Enums | — |
| 4 | Visit + User model updates | 2, 3 |
| 5 | Actions | 4 |
| 6 | QR service | — |
| 7 | Form requests | 4 |
| 8 | Controllers + routes | 5, 6, 7 |
| 9 | Seeder/factory | 4 |
| 10 | Frontend types + shared components | — |
| 11 | Visitor pages | 8, 10 |
| 12 | Admin pages | 8, 10 |
| 13 | Guard pages (scan, board) | 8, 10 |
| 14 | Emergency list + reports + staff | 8, 10 |
| 15 | Navigation + dashboard wiring | 8, 10 |
| 16 | Wayfinder generation + frontend build | 8, 11–15 |
| 17 | Feature/unit tests | 8 |
| 18 | Pint, PHPStan, type checks | 11–17 |

## 9. Known risks

- Browser camera support differs; manual code entry must always work.
- The project targets PHP 8.4 so it runs with the PHP version bundled with Herd.
