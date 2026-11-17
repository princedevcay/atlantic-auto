# Architecture

Status: DRAFT (pre-build, awaiting baseline approval)
Owner: developer (PrinceCaleb.dev)
Version: 0.1.0
Last reviewed: 2026-11-17

## Context and goals

Atlantic Auto and Shipping Services is a Ghanaian company importing vehicles from the USA. The system lets prospects request an import quote, lets customers request a vehicle update and view a private tracking page, and gives staff an admin to manage enquiries and shipments. Design decisions come from the approved Project Gate (`.opskeep/project-gate/DESIGN.md`); security decisions from `security-baseline.yaml`.

Constraints: Namecheap cPanel shared hosting (PHP, MySQL, cron, no Node or containers on the server), deploys over FTPS from GitHub Actions, public GitHub repo, mobile users on slow data.

Non-goals for v1: online payment, customer accounts, live GPS, CRM integration, languages other than English.

## System context

```
 Visitor / customer (phone browser)          Staff (browser, TOTP)
            |  HTTPS (TB-001)                        | HTTPS (TB-002)
            v                                        v
   +------------------------- cPanel account -------------------------+
   |  public/ (document root)  -> front controller index.php          |
   |  app/ (outside web root): routes, controllers, views, services   |
   |  CMP-001 public site        CMP-002 /admin                       |
   |        |  PDO (TB-003)            |  files (TB-003)              |
   |        v                          v                              |
   |  CMP-003 MySQL (localhost)   CMP-008 storage/uploads (private)   |
   |  CMP-005 cron: carrier sync, retention, backups, mail retry      |
   +--------|-------------------------------|-------------------------+
            | SMTP over TLS (TB-004)        | HTTPS allow-list (TB-004)
            v                               v
   CMP-004 cPanel mail -> shared inbox   CMP-006 carrier API (DEC-005)

   CMP-007 GitHub repo + Actions --FTPS (TB-005)--> app directory
```

## Components and ownership

| Component | Responsibility | Runtime/owner | Exposure | Data handled | Dependencies |
|---|---|---|---|---|---|
| CMP-001 Public site | Pages, quote and update forms, track lookup, tracking pages | PHP 8.2+ on cPanel / developer | INTERNET | PUBLIC content; CONFIDENTIAL form input | CMP-003, CMP-004 |
| CMP-002 Staff admin | Inbox tabs, shipments, stages, photos, staff accounts | PHP on cPanel / developer | INTERNET (login + TOTP) | CONFIDENTIAL, RESTRICTED (staff accounts) | CMP-003, CMP-008 |
| CMP-003 MySQL | Enquiries, shipments, stages, carrier events, staff, audit log, rate limits | cPanel MySQL / developer | PRIVATE (localhost) | CONFIDENTIAL, RESTRICTED | none |
| CMP-004 Email | Notification emails to the shared inbox | cPanel mail via PHPMailer / developer | THIRD_PARTY | CONFIDENTIAL | none |
| CMP-005 Cron jobs | Carrier sync, mail retry queue, retention deletes, backups | PHP CLI / developer | INTERNAL | CONFIDENTIAL | CMP-003, CMP-006 |
| CMP-006 Carrier API | Vessel, ETA and container events | Provider TBD (DEC-005) | THIRD_PARTY | INTERNAL (carrier references) | none |
| CMP-007 GitHub + Actions | Source, checks, Tailwind build, FTPS deploy | GitHub / developer | THIRD_PARTY | PUBLIC source, deploy secrets | none |
| CMP-008 Photo storage | Shipment photos outside the web root | cPanel file system / developer | PRIVATE | CONFIDENTIAL | none |

## Directory layout on the server (DEC-006)

```
~/atlantic-auto/            deployed by CI (FTPS account jailed here)
  public/                   document root: index.php, assets/ (built CSS, fonts, images)
  app/                      PHP source, views, migrations
  vendor/                   Composer dependencies (no dev packages)
~/atlantic-auto-private/    never touched by deploys
  config.php                DB, SMTP, carrier settings (from config.example.php)
  storage/uploads/          shipment photos
  storage/logs/             app and audit export logs
  storage/sessions/         PHP session files
  backups/                  nightly dumps, 14 days
```

If the domain is the account's primary domain and its document root cannot move from `public_html`, only `public/` is synced to `public_html` and `index.php` loads `app/` from `~/atlantic-auto/`. Either way, nothing but `public/` is web-reachable.

## Data flows and deployment

| Flow | Source to destination | Protocol / auth | Classification | Storage and retention | Failure behaviour |
|---|---|---|---|---|---|
| FLW-001 Quote request | Visitor to MySQL, email | HTTPS, CSRF + honeypot + rate limit | CONFIDENTIAL | enquiries table, DEC-004 | Input kept and retry offered; email queued if SMTP fails |
| FLW-002 Update request | Customer to MySQL, email | as above | CONFIDENTIAL | enquiries table, DEC-004 | as above; never confirms a VIN exists |
| FLW-003 Tracking page | MySQL to customer | HTTPS, link token or reference + last 4 digits | CONFIDENTIAL | none client-side, no-store | Generic not-found; last known carrier data shown |
| FLW-004 Staff actions | Staff to MySQL, storage | HTTPS, session + TOTP + role check + CSRF | CONFIDENTIAL | audit log | Safe error, no partial writes (transactions) |
| FLW-005 Carrier sync | Cron to carrier API to MySQL | HTTPS to allow-listed host, API key | INTERNAL | carrier_events | Backoff; staleness alert after 24h |
| FLW-006 Deploy | GitHub Actions to app directory | FTPS, environment secrets, owner approval | PUBLIC | Actions artifacts 90 days | Re-run previous commit to roll back |

Environments: local development (synthetic data) and production on cPanel. No staging in v1; adding one is a scope change.

Deployment identity: a dedicated cPanel FTP account limited to `~/atlantic-auto/`, used only by the GitHub `production` environment, which requires the owner's approval. The sync never deletes `~/atlantic-auto-private/`.

Backup and recovery: nightly `mysqldump` and uploads archive to `backups/` (14 days), weekly encrypted copy off the host (DEC-007), restore rehearsed before launch.

## Interfaces and integrations

- **Forms**: plain HTML POST, server-rendered responses, so they work without JavaScript.
- **Email**: PHPMailer over authenticated SMTP with TLS; subject prefixed `[Import Quote]` or `[Vehicle Update]`.
- **Carrier API**: provider chosen under DEC-005; 10 second timeout, exponential backoff, schema-checked responses, never overrides staff-set stages.
- **WhatsApp**: `wa.me` links only, no API integration.

## Decisions and change triggers

| Decision ID | Decision | Rationale | Alternatives | Security impact | Status |
|---|---|---|---|---|---|
| DEC-001 | TOTP MFA for all staff | Admin holds all customer data | Password only | Blocks most account takeovers | DECIDED |
| DEC-002 | Private link or reference + last 4 digits; no accounts | Occasional users; stage-level data | Customer accounts; VIN lookup | Limits exposure to one shipment | DECIDED |
| DEC-003 | Honeypot + rate limits, CAPTCHA only via change | No third-party script by default | Turnstile from day one | Keeps strict CSP | DECIDED |
| DEC-004 | Retention periods | Data minimisation | Keep forever | Lowers breach impact | OPEN (client) |
| DEC-005 | Carrier API provider | Needs client's shipping lines | Manual updates only | New third-party boundary | DEFERRED |
| DEC-006 | Code outside web root | Shared hosting exposure | Everything in public_html | Config and vendor never served | DECIDED |
| DEC-007 | Off-host backup destination | Host failure | Host backups only | Recovery from account compromise | OPEN |
| DEC-008 | Act 843 duties | Personal data of Ghanaians | None | Legal exposure | OPEN (client) |

Changes that need this document and the security baseline reassessed: adding payments, customer accounts or a CAPTCHA provider; choosing the carrier provider; any new third-party script; moving hosting; adding a staging environment; new data fields; changing who can see tracking pages.
