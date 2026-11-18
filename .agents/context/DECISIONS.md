# QRVisits — Decisions and Assumptions

## Confirmed by the user

- **D1 — Campus type:** school or university campus.
- **D2 — Name:** QRVisits.
- **D3 — Main pain point:** paper logbook at the guard house is slow and causes queues.
- **D4 — Roles:** guards and an admin operate the system. Visitors also have their own accounts and pre-register.
- **D5 — Approval:** the admin approves or denies every visit request before a pass is issued.
- **D6 — Walk-ins:** visitors register walk-ins themselves through the portal. The guard no longer creates walk-in records. The pass is issued immediately so it can be scanned at the gate.
- **D7 — Visitor data:** basic details only — full name, contact number, purpose, person/department to visit, date, time-in and time-out. A **valid ID document upload** was added at the user's request; no visitor photo is taken and no ID number is typed in.
- **D8 — Live monitoring:** the live "who is inside now" view and a printable emergency list are essential.
- **D9 — Devices:** desktop/laptop with webcam at the guard house; admin uses an office computer.
- **D10 — Context:** school capstone project; deadline not fixed yet, so build a tight, demo-ready first version.

## Technical decisions (implementation detail, low risk)

- **T1 — Pass code:** a short random uppercase code is generated when the admin approves a visit. The QR encodes that code, so manual entry is possible.
- **T2 — QR image:** rendered server-side as an SVG data URI with `bacon/bacon-qr-code`. No external image service is used.
- **T3 — Camera scanning:** uses the browser `BarcodeDetector` API where available (Chrome/Edge on the guard desktop), with a manual code input always available as the fallback.
- **T4 — Status model:** `pending → approved → inside → completed`, plus `denied`. A pass may only be scanned on its visit date.
- **T5 — Email verification:** not required to use QRVisits; admin approval is the security gate. Guards/admin accounts are created by an admin and marked verified.
- **T6 — Authorization:** all checks live in form requests, per project conventions. No policies or gates.
- **T7 — Roles:** stored as a `role` string column on `users`, cast to a PHP enum.
- **T8 — PHP version:** the project targets PHP 8.4 (`composer.json` `^8.4.0`, `.php-version` set to `8.4`) so it runs on the PHP version bundled with Herd. No installed package requires PHP 8.5.
- **T9 — ID document storage:** uploaded IDs are stored on the private `local` disk under `visitor-ids/`, never in the public folder. They are served through an authorised route to the visitor who owns the visit and to staff only. Uploads accept JPG, PNG or PDF up to 2 MB (the server's current upload limit). Files are deleted when a pending request is cancelled.

- **T10 - Expected time-out and overdue alerts:** `visits.expected_time_out` is required on new visitor requests and walk-ins. A visit is overdue when its status is `inside` and the current time is past the visit date + expected time-out. The guard/admin header polls `/alerts/overdue` every 30 seconds and shows a bell badge, dropdown and warning toast with visitor name, pass code, expected time-out and minutes late. Alerts disappear once the visitor is checked out. This requires no scheduler.

- **T11 - Self-service walk-ins:** walk-ins are visitor-initiated, auto-approved, and issued a pass immediately (`status = approved`, unique pass code). The visitor's name comes from their account; contact number can be corrected on the form. Guards only scan the pass, and staff access to the walk-in form is denied.

## Assumptions

- **A1 — Visitors can only see their own requests.**
- **A2 — Guards can see contact numbers for approved visits but cannot approve, deny or edit them.**
- **A3 — One pass equals one visit day; a returning visitor submits a new request.**
- **A4 — Personal data retention will follow the school's policy; no automatic purge in version 1.**
- **A5 — The gate has a stable internet connection.**
