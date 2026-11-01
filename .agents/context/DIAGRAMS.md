# QRVisits — Context and Data Flow Diagrams

> **Interactive version:** open **`/context/erd`** in the running app for the
> ERD, database structure tables and a print button.

Mermaid diagrams for the capstone documentation. They render automatically on
GitHub, GitLab, and VS Code with a Mermaid preview extension.

- **Level 0** — Context diagram: who talks to the system.
- **Level 1** — Data flow diagram: the main processes and data stores.
- **Level 2** — Gate scanning detail (the security-critical process).

Legend: rectangle = external entity, rounded box = process, cylinder = data
store, arrow = data flow.

---

## Level 0 — Context diagram

```mermaid
flowchart LR
    VISITOR["Visitor<br/>(student, parent, supplier, applicant)"]
    GUARD["Guard<br/>(gate house)"]
    ADMIN["Admin<br/>(campus office)"]
    SYS(("QRVisits<br/>Campus Visitor<br/>Registration &<br/>Security Monitoring"))

    VISITOR -- "account details, visit request,<br/>purpose, date, contact number, valid ID" --> SYS
    SYS -- "request status, QR pass,<br/>denial reason" --> VISITOR

    GUARD -- "QR pass scan, typed pass code,<br/>walk-in details" --> SYS
    SYS -- "time-in / time-out result,<br/>visitor details, who-is-inside list" --> GUARD

    ADMIN -- "approval or denial, denial reason,<br/>staff account details, report filters" --> SYS
    SYS -- "pending requests, approved pass,<br/>live board, emergency list, reports, CSV" --> ADMIN
```

### External entities

| Entity | What they provide | What they receive |
| --- | --- | --- |
| Visitor | Account details, visit request (purpose, host, date, contact, valid ID) | Request status, QR pass, denial reason |
| Guard | QR scan / typed pass code, walk-in registration | Check-in/out outcome, visitor details, live board |
| Admin | Approve/deny decisions, staff account details, report filters | Approval queue, live board, emergency list, reports, CSV |

### System boundary

Inside QRVisits: accounts and roles, visit records, QR pass generation, gate
scanning, live monitoring, reports, and private ID storage.

Outside QRVisits (version 1): SMS/email providers, offline gate operation,
multi-campus/multi-gate support, and identity-document verification services.

---

## Level 1 — Data flow diagram

```mermaid
flowchart TB
    VISITOR["Visitor"]
    GUARD["Guard"]
    ADMIN["Admin"]

    P1([1.0<br/>Register &<br/>authenticate])
    P2([2.0<br/>Submit visit<br/>request])
    P3([3.0<br/>Approve or<br/>deny visit])
    P4([4.0<br/>Issue QR<br/>pass])
    P5([5.0<br/>Record gate<br/>scan])
    P6([6.0<br/>Register<br/>walk-in])
    P7([7.0<br/>Monitor live<br/>board &<br/>emergency list])
    P8([8.0<br/>Generate<br/>reports])
    P9([9.0<br/>Manage staff<br/>accounts])

    D1[(D1 Users<br/>role, contact)]
    D2[(D2 Visits<br/>status, pass code, times)]
    D3[(D3 ID documents<br/>private files)]

    VISITOR -- "sign-up, login" --> P1
    P1 -- "session" --> VISITOR
    P1 <--> D1

    VISITOR -- "visit details + ID upload" --> P2
    P2 -- "pending visit" --> D2
    P2 -- "stored file" --> D3
    P2 -- "request status" --> VISITOR

    ADMIN -- "approve / deny + reason" --> P3
    P3 -- "status = approved or denied" --> D2
    P3 -- "approved visit" --> P4

    P4 -- "pass code + QR" --> D2
    P4 -- "QR pass" --> VISITOR

    GUARD -- "scan or type pass code" --> P5
    D2 -- "matching visit" --> P5
    P5 -- "status, time-in, time-out, guard" --> D2
    P5 -- "outcome + visitor details" --> GUARD

    GUARD -- "walk-in details" --> P6
    P6 -- "pending walk-in" --> D2
    P6 -- "approval needed" --> ADMIN

    D2 -- "visitors currently inside" --> P7
    P7 -- "live board / printable list" --> GUARD
    P7 -- "live board / emergency list" --> ADMIN

    ADMIN -- "date range" --> P8
    D2 -- "visit records" --> P8
    P8 -- "summary, table, CSV" --> ADMIN

    ADMIN -- "name, email, role, password" --> P9
    P9 -- "guard / admin account" --> D1
```

### Processes

| No. | Process | Main input | Main output |
| --- | --- | --- | --- |
| 1.0 | Register and authenticate | Account details, credentials | Session, user record |
| 2.0 | Submit visit request | Visitor details, visit date, ID file | Pending visit, stored ID |
| 3.0 | Approve or deny visit | Admin decision, reason | Updated visit status |
| 4.0 | Issue QR pass | Approved visit | Pass code and QR image |
| 5.0 | Record gate scan | Scanned/typed code | Time-in or time-out, outcome message |
| 6.0 | Register walk-in | Guard-entered visitor details | Pending walk-in visit |
| 7.0 | Monitor live board and emergency list | Visits with status `inside` | Live list, printable list |
| 8.0 | Generate reports | Date range | Totals, visit table, CSV |
| 9.0 | Manage staff accounts | Admin-entered account details | Guard/admin user records |

### Data stores

| Store | Contents | Who can read it |
| --- | --- | --- |
| D1 Users | Name, email, password hash, role (`visitor`/`guard`/`admin`), contact number | The user themselves; admins see staff accounts |
| D2 Visits | Visitor name/contact, purpose, host, visit date, status, pass code, denial reason, walk-in flag, approval and scan audit fields | Owner (own visits); guards and admins (all visits) |
| D3 ID documents | Uploaded JPG/PNG/PDF files on the private disk | Owner of the visit; guards and admins |

---

## Level 2 — Gate scanning (process 5.0)

```mermaid
flowchart LR
    GUARD["Guard"]
    CAM["Camera<br/>(webcam)"]
    P51([5.1<br/>Read pass<br/>code])
    P52([5.2<br/>Validate<br/>pass])
    P53([5.3<br/>Record time-in])
    P54([5.4<br/>Record time-out])
    P55([5.5<br/>Report<br/>outcome])
    D2[(D2 Visits)]

    GUARD -- "start scan or<br/>type code" --> P51
    CAM -- "QR image" --> P51
    P51 -- "pass code" --> P52
    D2 -- "visit for this code" --> P52

    P52 -- "not found / pending / denied /<br/>wrong date / completed" --> P55
    P52 -- "approved for today" --> P53
    P52 -- "currently inside" --> P54

    P53 -- "status inside, time-in, guard" --> D2
    P54 -- "status completed, time-out, guard" --> D2

    P53 --> P55
    P54 --> P55
    P55 -- "success or refusal message,<br/>visitor summary" --> GUARD
```

### Validation outcomes shown to the guard

| Condition | Result |
| --- | --- |
| Code does not exist | "Pass not found" |
| Request still pending | "Not approved yet" |
| Request denied | "Visit was denied" |
| Pass used on another date | "Not valid today" |
| Visit already completed | "Already completed" |
| First valid scan of the day | Time-in recorded, visitor appears on the live board |
| Second valid scan | Time-out recorded, visitor leaves the live board |

---

## Notes for the documentation chapter

- A QR pass encodes only the short pass code; no personal data is in the QR.
- Passes become valid only after admin approval and only on the visit date.
- ID documents never sit in the public web folder and are served through an
  authorised route.
- Version 1 assumes a stable internet connection at the gate; there is no
  offline queue.
