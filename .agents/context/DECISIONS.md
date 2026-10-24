# QRVisits — Decisions and Assumptions

## Confirmed by the user

- **D1 — Campus type:** school or university campus.
- **D2 — Name:** QRVisits.
- **D3 — Main pain point:** paper logbook at the guard house is slow and causes queues.
- **D4 — Roles:** guards and an admin operate the system. Visitors also have their own accounts and pre-register.
- **D5 — Approval:** the admin approves or denies every visit request before a pass is issued.
- **D6 — Walk-ins:** the guard registers a walk-in on the spot and the admin approves it quickly.
- **D7 — Visitor data:** basic details only — full name, contact number, purpose, person/department to visit, date, time-in and time-out. No photos, ID numbers or addresses.
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

## Assumptions

- **A1 — Visitors can only see their own requests.**
- **A2 — Guards can see contact numbers for approved visits but cannot approve, deny or edit them.**
- **A3 — One pass equals one visit day; a returning visitor submits a new request.**
- **A4 — Personal data retention will follow the school's policy; no automatic purge in version 1.**
- **A5 — The gate has a stable internet connection.**
