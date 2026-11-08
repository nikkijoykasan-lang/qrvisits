# QRVisits — Features

Status values: `planned`, `in progress`, `done`, `deferred`.

## Accounts and access

| Feature | Status | Notes |
| --- | --- | --- |
| Visitor sign-up with contact number | done | Public registration; role defaults to `visitor`; contact number requested per visit |
| Role-based access (visitor / guard / admin) | done | Enforced in form requests |
| Admin creates guard and admin accounts | done | Staff accounts screen |
| Guard/admin removed only by an admin | done | Cannot remove own account |

## Visits and passes

| Feature | Status | Notes |
| --- | --- | --- |
| Visitor submits a visit request | done | Purpose, host, date, contact number, valid ID |
| Visitor sets an expected time-out | done | Required on visitor requests; guards set it for walk-ins |
| Visitor uploads a valid ID | done | JPG, PNG or PDF up to 2 MB; stored privately; visible to the owner and staff |
| Admin approves a request and issues a pass | done | Random pass code generated at approval |
| Admin denies a request with a reason | done | Reason shown to the visitor |
| Visitor views and prints a QR pass | done | QR generated server-side as an SVG data URI |
| Pass valid only for its visit date | done | Guard sees a clear "wrong date" message |
| Visitor cancels a pending request | done | Only while pending |
| Guard registers a walk-in visitor | done | Guard enters basic details; admin approves |

## Gate and monitoring

| Feature | Status | Notes |
| --- | --- | --- |
| Gate scan screen with webcam | done | BarcodeDetector with manual code fallback |
| First scan records time-in, second records time-out | done | Clear outcomes for denied/expired/already done |
| Live "inside now" board | done | Polls every 15 seconds with partial reloads |
| Today's expected visitors | done | Approved visits for today |
| Printable emergency visitor list | done | Standalone printable page |
| Automated overdue alerts | done | Header bell + toast for guards/admins when an inside visitor passes their expected time-out; shows name, pass code (ID), expected time-out and minutes late |

## Records and reports

| Feature | Status | Notes |
| --- | --- | --- |
| Visit history with filters | done | Date, status, search, pagination |
| Visit detail with audit info | done | Approver, guard, time-in/out |
| Daily report summary and CSV export | done | Date range with summary cards |

## Deferred (not version 1)

| Feature | Status | Notes |
| --- | --- | --- |
| Overstay alerts | deferred | Live in-app overdue alerts are implemented; SMS/email escalation is deferred |
| Visitor photo / ID capture | deferred | Visitor photos are not captured; uploaded ID documents are supported |
| Host/staff approval role | deferred | Adds another account type |
| Email/SMS notifications | deferred | Needs mail provider |
| Offline scanning | deferred | Out of scope |
| Multi-gate support | deferred | Out of scope |
