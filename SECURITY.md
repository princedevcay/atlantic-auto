# Security

Status: APPROVED 0.3.0 on 18 Nov 2026 (CHG-003 open launch; 0.2.0 and 0.1.0 approved earlier). Scope: development, production:namecheap-cpanel
Design digest: 0fdc28433c05edd0f656b338b74a33cbd41d51dc71ddf38dd08514ab2b43f383
Baseline version: 0.3.0
Canonical record: `security-baseline.yaml`
Owner: developer (PrinceCaleb.dev)
Last reviewed: 2026-11-17

This file is the human-readable view. `security-baseline.yaml` is authoritative for approval, scope, locks, controls, gates and evidence. The design is approved; nothing below is implemented or tested yet. Every control is `PLANNED` and every gate `NOT_YET_BUILT`, so production deploys stay blocked by GATE-002 to GATE-004 until their evidence passes.

## Reporting a vulnerability

Please use GitHub's private vulnerability reporting on this repository (Security tab, "Report a vulnerability"). Do not open a public issue. Reports are acknowledged within 3 working days; high and critical issues are fixed within 14 days, and internet-reachable critical issues within 72 hours.

## Posture and scope

- Risk: **MEDIUM / STANDARD**. Staff login and admin, customer personal data, private tracking pages, a carrier API and staff photo uploads. No payments and no AI.
- Environments: development (synthetic data) and production on Namecheap cPanel. Proposed approval scope: `development`, `production:namecheap-cpanel`.
- Locks: development and deployment `UNLOCKED` by the owner's approval of 0.3.0. GATE-005 live checks follow within 48 hours of the first public deploy.
- Excluded from v1: payments, customer accounts, live GPS, staging environment.

## Assurance targets and standards

ASVS 5.0 Level 2 target, SLSA build level 1 and source level 2 (this skill's policy minimums for MEDIUM). Controls carry references to OWASP Top 10:2025, ASVS 5.0 chapters, NIST SSDF 1.1, SLSA 1.2 and CISA Secure by Design. These are mappings of topics covered, not conformance claims. AI and agent controls do not apply: there is no model or agent in the system.

## Baseline controls

| Control ID | Requirement (short) | Owner | Status | Evidence | Next step |
|---|---|---|---|---|---|
| CTL-GOV-001 | No build or deploy before baseline approval; CI checks the approved digest | developer | PLANNED | EVD-001 / NOT_YET_BUILT | Owner approval |
| CTL-AUTH-001 | Argon2id/bcrypt hashes, 12+ characters, TOTP MFA for all staff, admin-only resets | developer | PLANNED | EVD-004 | Build |
| CTL-AUTH-002 | Login throttling and lockout alerts | developer | PLANNED | EVD-004 | Build |
| CTL-AUTHZ-001 | Default-deny admin middleware with role checks per action | developer | PLANNED | EVD-002 | Build + tests |
| CTL-AUTHZ-002 | Tracking by 128-bit link token or random reference + last 4 digits; never VIN alone | developer | PLANNED | EVD-002 | Build + tests |
| CTL-SES-001 | Secure, HttpOnly, SameSite=Strict sessions; timeouts; CSRF on every form | developer | PLANNED | EVD-004 | Build |
| CTL-INP-001 | Server validation, PDO prepared statements only, escaped output, no header injection | developer | PLANNED | EVD-002 | Build + tests |
| CTL-API-001 | Honeypot, fill-time and rate limits on forms and lookups | developer | PLANNED | EVD-004 | Build |
| CTL-API-002 | Carrier calls to allow-listed host only, timeouts, schema checks | developer | PLANNED | EVD-004 | After DEC-005 |
| CTL-FILE-001 | Image uploads checked by content, 8 MB, re-encoded, stored outside web root | developer | PLANNED | EVD-004 | Build |
| CTL-FE-001 | Strict CSP, no third-party scripts, nothing sensitive in the browser | developer | PLANNED | EVD-004 | Build |
| CTL-CRY-001 | HTTPS + HSTS, FTPS deploys, SMTP over TLS, random_bytes tokens | developer | PLANNED | EVD-004 | Build |
| CTL-SEC-001 | No secrets in the public repo; push protection and gitleaks | developer | PLANNED | EVD-002 | Enable on GitHub |
| CTL-SEC-002 | FTP secrets only in the GitHub production environment; jailed FTP account; least-privilege DB user | developer | PLANNED | EVD-005 | Owner adds secrets |
| CTL-CFG-001 | Code and config outside web root; errors hidden; security headers; PHP 8.2+ | developer | PLANNED | EVD-004 | Build |
| CTL-DATA-001 | Minimal fields, privacy notice, retention job, deletion on request | client owner | PLANNED | EVD-004 | DEC-004, DEC-008 |
| CTL-DATA-002 | Tracking pages and emails show only what is needed; VIN masked | developer | PLANNED | EVD-004 | Build |
| CTL-ERR-001 | Safe errors, no false success, queued email retries | developer | PLANNED | EVD-004 | Build |
| CTL-LOG-001 | Audit log of staff actions; no secrets or full contact details in logs; alerts | developer | PLANNED | EVD-004 | Build |
| CTL-SC-001 | Lockfiles, audits fail on HIGH, actions pinned to SHAs, Dependabot | developer | PLANNED | EVD-002 | Build CI |
| CTL-DEP-001 | Protected main, owner-approved production environment, safe sync excludes, rollback | developer | PLANNED | EVD-003 | Build CI |
| CTL-REC-001 | Nightly backups, weekly off-host copy, restore rehearsal | developer | PLANNED | EVD-004 | DEC-007 |

## Identity and permissions

| Identity | Can | Cannot |
|---|---|---|
| Admin (ID-001) | Everything Staff can, plus manage staff, delete records, export | Read secrets, deploy |
| Staff (ID-002) | Enquiries, shipments, stages, photos, copy tracking links | Manage staff, delete, export all, change carrier settings |
| Customer (ID-003) | Submit forms, view own tracking page | See other shipments, full contact details, prices, internal notes |
| Deploy workflow (ID-004) | Upload code to the app directory | Touch config, uploads, backups, database |
| Carrier sync (ID-005) | Read carrier references, insert events | Change staff-set stages, call other hosts |
| Developer (ID-006) | Merge, approve deploys, manage cPanel and secrets | Commit secrets, use real customer data locally |

## Supply chain and vulnerability management

Lockfiles required; Composer and npm audits block HIGH and CRITICAL; GitHub Actions pinned to commit SHAs; Dependabot weekly. SBOM not produced (small dependency set; lockfiles are the inventory). Build provenance planned with GitHub artifact attestations. Remediation within 14 days, 72 hours for exploitable critical issues, for the life of the support agreement.

## Regulatory context

- **Ghana Data Protection Act, 2012 (Act 843)**: NOT_ASSESSED. The client confirms registration and privacy notice duties before launch (DEC-008). Addressed by CTL-DATA-001 and CTL-DATA-002.
- **PCI DSS**: NOT_APPLICABLE. No card data.
- **Client agreement** and **carrier API terms**: NOT_ASSESSED.

This is not legal advice.

## Gates and evidence

| Gate | Stage | Blocks | Status |
|---|---|---|---|
| GATE-001 Baseline approval | PRE_IMPLEMENTATION | yes | NOT_YET_BUILT (owner approval recorded as EVD-001; CI check not built) |
| GATE-002 PR checks: secrets, audits, lint, access-control tests | PULL_REQUEST | yes | VERIFIED (EVD-002) |
| GATE-003 Production deploy: protected main, owner approval, digest check | PRE_DEPLOY | yes | Evidence passed (EVD-003) |
| GATE-004 Pre-launch security review (local build) | PRE_DEPLOY | yes | VERIFIED (EVD-004) |
| GATE-005 Live launch checks, within 48 hours of the first public deploy | RUNTIME | yes | NOT_YET_BUILT (EVD-005, EVD-006) |

## Dependency audit exceptions

CI fails on any HIGH or CRITICAL npm advisory except owner-approved exceptions in `.github/audit-exceptions.json` (CHG-001). Each names one advisory and stops counting on its expiry date or when the package publishes a newer release.

| Advisory | Package | Why it is allowed | Ends |
|---|---|---|---|
| GHSA-fc5q-39xc-vjpf | picomatch (ReDoS) | No patched release exists. Build-time only, via the Tailwind CLI's file watcher; nothing from npm is deployed and watch mode is not used | 2 Dec 2026, or when picomatch releases a version newer than 4.0.7 |

## Known risks and open decisions

- DEC-004 retention periods (client), DEC-007 off-host backup destination, DEC-008 Act 843 duties (client): open, needed before launch, not blocking design approval.
- DEC-005 carrier provider: deferred; choosing it is recorded as a change before the integration is built.
- Residual: shared hosting means other tenants share the server; mitigated by keeping code and data outside the web root and least-privilege accounts. Backups on the same host are not enough on their own (THR-011 residual MEDIUM until DEC-007 is closed).
- The repo is public, so this design is public. Security relies on the controls, not on secrecy.

## Response runbooks

### Staff account compromise
Disable the account, end its sessions, reset password and TOTP, review the audit log, rotate any secret the account could reach, inform the client.

### Form abuse
Tighten rate limits, check the honeypot, consider a CAPTCHA through a change record.

### Carrier outage
Pages keep showing last known data with its time; staff update stages manually.

### Outage
Check cPanel status and the error log; roll back by re-running the previous deploy.

### Errors
Read `storage/logs`; never enable display_errors on production.
