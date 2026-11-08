# QRVisits — Project Overview

## What it is

QRVisits is a web-based campus visitor registration and security monitoring system. Visitors pre-register online, an admin approves the visit, the visitor receives a QR pass, and guards scan that pass at the gate to record time-in and time-out.

## Problem being solved

Visitors are logged in a paper notebook at the guard house. Handwriting is slow, queues form at the gate, records are hard to read and search, and there is no instant answer to "who is inside the campus right now?" during an emergency.

## Goals

1. Make gate registration fast — a pre-registered visitor is scanned in, in seconds.
2. Keep a complete digital record of every visit (time-in, time-out, who approved, who scanned).
3. Give security a live view of everyone currently inside the campus, plus a one-click printable emergency list.
4. Let the admin screen visitors before they arrive through an approval step.
5. Produce simple reports that replace manual tallying.

## Scope (version 1)

- One campus, one gate.
- Visitors register an account and request a visit for a specific date, including a valid ID upload.
- Admin approves or denies requests and issues QR passes.
- Guards scan passes with a webcam, with manual code entry as a fallback.
- Walk-in visitors are registered by the guard and approved by the admin.
- Live "inside now" board, printable emergency list, visit history and simple reports.
- Guard/admin accounts are created by the admin.

## Out of scope (version 1)

- Offline scanning.
- Multiple gates or multiple campuses.
- Visitor photos, ID number capture, vehicle plates.
- ID document scanning / OCR.
- SMS or email notifications of approval.
- Host/staff approval role.
- Student attendance QR codes.

## Limitations and assumptions

- Requires an internet connection at the gate.
- Overdue alerts are in-app only (header bell + toast) and poll every 30 seconds while the app is open; there is no SMS/email escalation yet.
- ID uploads are limited to 2 MB by the current PHP upload configuration.
- Information is typed at registration; the system does not verify identity documents.
- One QR pass is valid for the visit date it was approved for.
- Visitors do not need a verified email address to use the system (approval by the admin is the security control); guards and admin accounts are created by an admin.
- Visitor personal data is deliberately minimal (name, contact number, purpose, host, date), visible only to guards and admins.
