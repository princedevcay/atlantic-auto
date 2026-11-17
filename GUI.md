# GUI: interface handoff

Status: DRAFT (pre-build)
Owner: developer (PrinceCaleb.dev)
Last reviewed: 2026-11-17
Applies to: public website and staff admin (web, mobile-first)

Visual decisions are owned by the approved Project Gate: read `.opskeep/project-gate/DESIGN.md`, `screens.md`, `components.md`, `responsive.md` and `accessibility.md` first. This file adds capability status, backend contracts, permissions and hard boundaries. Nothing is built yet.

## Experience intent

Ghanaian buyers importing a car from the USA, mostly on phones and mobile data, visiting a few times. Two jobs: Request an Import Quote and Request a Vehicle Update, plus checking a tracking page. Staff use a desktop-first admin daily. Tone: trustworthy and plain. Non-goals: payments, customer accounts, live GPS.

## Capability matrix

| Capability | Route/surface | Roles | Backend contract | Status | States covered | Evidence | Limitation/next step |
|---|---|---|---|---|---|---|---|
| Landing page (12 sections) | `/` | public | server-rendered | NOT BUILT | default, missing image | NOT_YET_BUILT | Client images pending |
| Services, How It Works, About, Contact, Privacy | `/services`, `/how-it-works`, `/about`, `/contact`, `/privacy` | public | server-rendered | NOT BUILT | default | NOT_YET_BUILT | Contact details pending |
| Request an Import Quote | `/quote` | public | `POST /quote` | NOT BUILT | default, invalid, rate-limited, failed, success | NOT_YET_BUILT | |
| Request a Vehicle Update | `/update` | public | `POST /update` | NOT BUILT | default, invalid, rate-limited, failed, success | NOT_YET_BUILT | |
| Track lookup | `/track` and home quick box | public | `POST /track` | NOT BUILT | default, not found (generic), rate-limited | NOT_YET_BUILT | |
| Tracking page | `/t/{token}` | customer with token or verified lookup | `GET /t/{token}` | NOT BUILT | default, revoked/not found, carrier stale | NOT_YET_BUILT | Carrier provider pending (DEC-005) |
| Staff sign-in + TOTP | `/admin/login`, `/admin/mfa` | staff, admin | `POST /admin/login`, `POST /admin/mfa` | NOT BUILT | default, wrong credentials, locked, session expired | NOT_YET_BUILT | |
| Inbox with tabs | `/admin/inbox` (tabs: all, quotes, updates) | staff, admin | `GET /admin/inbox` | NOT BUILT | loading, empty, populated, forbidden | NOT_YET_BUILT | |
| Shipments list and detail | `/admin/shipments`, `/admin/shipments/{id}` | staff, admin | `GET/POST /admin/shipments...` | NOT BUILT | empty, populated, saving, saved, error | NOT_YET_BUILT | |
| Photo upload | shipment detail | staff, admin | `POST /admin/shipments/{id}/photos` | NOT BUILT | uploading, rejected type/size, success | NOT_YET_BUILT | |
| Tracking link copy/regenerate | shipment detail | staff, admin | `POST /admin/shipments/{id}/link` | NOT BUILT | success, revoked | NOT_YET_BUILT | |
| Staff management | `/admin/staff` | admin only | `GET/POST /admin/staff...` | NOT BUILT | forbidden for staff | NOT_YET_BUILT | |

## Backend contracts consumed by the UI

All routes are server-rendered PHP. Every POST carries a CSRF token. The server is authoritative for validation and permissions.

| Operation | Request | Success | Expected errors | UI behaviour |
|---|---|---|---|---|
| `POST /quote` | name, phone, email?, services[], year, make, model, source (auction/dealer/private), listing_url?, notes?, honeypot, csrf | redirect to success with reference | 422 field errors, 429 rate limit, 500 | Keep input; field errors inline with summary; friendly wait message on 429 |
| `POST /update` | name, phone, vin_or_reference, question, honeypot, csrf | success with reference; tracking link only if the session already proved access | 422, 429, 500 | Never says whether the VIN exists |
| `POST /track` | reference, phone_last4, csrf | redirect to `/t/{token}` | one generic "not found" message, 429 | Same message for every failure |
| `GET /t/{token}` | token | tracking view | 404 (generic) | No-store, noindex |
| `POST /admin/login` then `/admin/mfa` | email, password, csrf; then code, csrf | admin home | generic invalid, locked (with wait time), session expired | No hint which field was wrong |
| Admin mutations | per form, csrf | redirect with toast | 403, 404, 422, 409 | 403 shows a plain "not allowed" page |

## UI-relevant data models

| Model/field | Format | Required | Classification | Display rule | Validation owner |
|---|---|---|---|---|---|
| Enquiry.phone | +233 or international, 9 to 15 digits | yes | CONFIDENTIAL | Admin only | Server |
| Enquiry.email | email | quote: no | CONFIDENTIAL | Admin only | Server |
| Enquiry.vin_or_reference | 17-char VIN or reference | update: yes | CONFIDENTIAL | Admin only | Server |
| Shipment.vin | 17 chars | yes | CONFIDENTIAL | Tracking page: last 6 only | Server |
| Shipment.stage | Sourcing, Purchased, Picked up, Shipped, Clearing, Delivered | yes | CONFIDENTIAL | Timeline | Server |
| Shipment.reference | 8 random characters | yes | CONFIDENTIAL | Shown to customer and staff | Server generated |
| CarrierEvent.text | plain text | no | INTERNAL | Escaped; labelled "from carrier" with time | Server |

## Authentication and permissions

- Customers have no accounts. Access to a tracking page is a link token or a verified lookup.
- Staff sign in with email, password and a TOTP code. Sessions expire after 30 minutes idle or 8 hours total; an expired session returns to login with the page they wanted after re-auth.
- Admin-only screens (staff management, delete, export) are hidden for Staff, but the server also returns 403. Hiding a control is never the protection.
- Deleting a shipment or disabling a staff member needs a typed confirmation.

## Sample interfaces

1. **Public shell**: skip link, top bar, main region, footer with contact details, sticky mobile action bar (see DESIGN.md).
2. **Admin inbox**: tabs All, Import Quotes, Vehicle Updates with new counts; table rows; detail drawer; empty state "No new requests"; error state with retry.
3. **Quote form**: labels above fields, hints, inline errors linked by `aria-describedby`, error summary focused on submit failure, submit button disabled while sending, success panel with reference and WhatsApp link.

## Design and interface tokens

Approved tokens live in `.opskeep/project-gate/tokens.json` (Harbour Trust) and map into the Tailwind theme. Do not use the template's neutral sample tokens.

## Responsive and accessibility requirements

As in the Project Gate `responsive.md` and `accessibility.md`: mobile-first, 44px targets, 320px width with no horizontal scroll, WCAG 2.2 AA target, keyboard and VoiceOver passes before release. No conformance is claimed until tested.

## Analytics, audit and privacy

No third-party analytics in v1. Server audit events: staff login, failure, MFA change, role change, stage change, deletion, export, tracking link regenerate. Never log passwords, tokens or full phone numbers.

## Hard boundaries for implementers

Interface work may refine presentation within the approved tokens and contracts. It must not silently change routes, fields, server validation, permissions, tracking access rules, retention, audit events, security headers or CSP, or add third-party scripts (fonts, analytics, CAPTCHA, chat widgets, map embeds). Any such need goes through a change proposal with the old rule, new rule, reason, impact and affected IDs, approved by the owner.
