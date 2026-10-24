# QRVisits — Users and Responsibilities

## Visitor
- Creates an account with name, email, password and contact number.
- Submits a visit request: purpose, person/department to visit, visit date, and a valid ID upload.
- Sees the status of each request and the QR pass once approved.
- Shows the QR pass at the gate.
- Can cancel a request that is still pending.
- Sees only their own requests and passes.

## Guard
- Scans a visitor's QR pass at the gate: first scan records time-in, next scan records time-out.
- Can type a pass code manually when the camera or QR is not readable.
- Registers walk-in visitors who arrive without pre-registering.
- Sees the live "inside now" board and today's expected visitors.
- Sees basic visitor details and contact numbers for approved visits, but cannot approve, deny or edit them.

## Admin
- Reviews pending visit requests and approves or denies them (with a reason).
- Issues the QR pass by approving the visit.
- Registers walk-in visitors as an alternative to the guard.
- Sees the live board, the emergency list, visit history and reports.
- Creates and removes guard/admin accounts.
- Cannot remove their own account from the staff screen.

## Access summary

| Area | Visitor | Guard | Admin |
| --- | --- | --- | --- |
| Own visit requests | yes | - | - |
| Approve / deny visits | - | - | yes |
| Gate scanning | - | yes | yes |
| Walk-in registration | - | yes | yes |
| Live board / emergency list | - | yes | yes |
| Reports | - | - | yes |
| Manage staff accounts | - | - | yes |
