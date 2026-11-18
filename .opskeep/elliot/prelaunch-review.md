# Pre-launch security review (GATE-004, EVD-004)

Baseline 0.2.0 (digest `fa7e6833...`), reviewed 18 Nov 2026 on a local build (PHP 8.4, MariaDB 10.11, and Apache in Docker for the server rules). Under baseline 0.3.0 (CHG-003, open launch) this local review is GATE-004 and its evidence EVD-004 is **PASSED**. The live checks move to **GATE-005**, run within 48 hours of the first public deploy (EVD-005, EVD-006).

## Gate status

| Gate | Status | Evidence |
|---|---|---|
| GATE-001 Baseline approval | Approval recorded; CI check built into the deploy workflow | EVD-001 PASSED |
| GATE-002 Pull request checks | **VERIFIED**: green run on 09671aa, a blocked run (dependency audit), and a direct push to main refused by branch protection | EVD-002 PASSED |
| GATE-003 Production deploy | Deploy from a non-main branch refused; owner approval and main-only environment; digest check | EVD-003 PASSED |
| GATE-004 Pre-launch review (local build) | Local review done (this file) | EVD-004 PASSED |
| GATE-005 Live launch checks | Within 48 hours of the first public deploy | EVD-005, EVD-006 not yet run |

## Controls checked locally

| Control | Result | How it was checked |
|---|---|---|
| CTL-AUTH-001 hashes, TOTP, resets | Pass | Tests: password alone is refused, TOTP completes sign-in; password hashes use Argon2id |
| CTL-AUTH-002 throttling, lockout | Pass | Test: five failures lock the account, the right password is still refused while locked; lockout alert queued |
| CTL-AUTHZ-001/002/003 access control | Pass (VERIFIED) | Tests: admin routes need sign-in; Staff get 403 on Admin actions; hidden cars and their photos 404; tracking 404 while switched off |
| CTL-SES-001 sessions, CSRF | Pass locally | Tests: CSRF enforced on every form; session cookie HttpOnly; Secure and `__Host-` apply only over HTTPS, so check live |
| CTL-INP-001 validation, encoding | Pass (VERIFIED) | Tests: SQL injection and script strings stored as text and shown escaped; header injection rejected |
| CTL-API-001 form abuse | Pass | Tests: honeypot dropped; sixth quote from one connection in an hour gets 429 |
| CTL-API-002 carrier calls | Not applicable yet | No carrier integration exists (DEC-005 deferred) |
| CTL-FILE-001 uploads | Pass | Test: a PHP script renamed to .jpg is rejected and not stored; uploads live outside the web root and are served through access-checked handlers |
| CTL-FE-001 CSP, scripts | Pass | Tests: CSP present, no third-party scripts, no inline styles |
| CTL-CFG-001 headers, layout | Pass locally | Apache in Docker: app, vendor, bin, database, composer files, dotfiles and deploy state return 404 with either document root; directory listing off |
| CTL-CRY-001 HTTPS, FTPS, TLS | Partly checked | HTTP and www redirect to HTTPS (Apache test); FTPS set in the deploy workflow; certificate, HSTS and SMTP over TLS need the live server |
| CTL-ERR-001 failure handling | **Fixed, then pass** | Finding: with the database down, a form lost the visitor's input behind an error page. Now the form returns with their details and a retry message (503) |
| CTL-LOG-001 logging | Pass | Logs searched: no phone numbers, emails, VINs, passwords or tokens; audit log records staff actions |
| CTL-DATA-002 minimal exposure | Pass | Notification emails carry no tracking tokens; car VINs are staff only |
| CTL-REC-001 backup and restore | **Fixed, then pass locally** | Finding: the nightly backup could not be restored (tables in alphabetical order broke a foreign key). Fixed; a restore into an empty database now matches every table row for row; uploads archive created, owner-only |
| CTL-SC-001, CTL-SEC-001 supply chain, secrets | Pass (VERIFIED) | CI: secret scan, Composer and npm audit gate with the CHG-001 exception, pinned actions |
| CTL-DEP-001 deploy path | Pass | Branch protection; main-only, owner-approved environment; non-main deploy refused |

## Before the first deploy (owner setup in cPanel and GitHub)

1. Document root `atlanticautoservices.com/public` (recommended), PHP 8.2 or later, AutoSSL active.
2. Database and a user limited to it; dedicated FTP account whose directory is `atlanticautoservices.com`.
3. FTP secrets in the GitHub production environment; `~/atlantic-auto-private/config.php` with `chmod 600`.

## GATE-005: live checks after the first public deploy (EVD-005, EVD-006)

First deploy on 18 Nov 2026 (run 44313791210, commit b1573f1). Checked the same day:

| Check | Result | How |
|---|---|---|
| FTP account limited to `atlanticautoservices.com`; database user limited to its own database | Pass (EVD-005) | Owner inspected in cPanel and confirmed |
| HTTPS certificate for the domain and www | Pass | SSL.com certificate, valid to 4 Jun 2027, both names |
| HTTP and www redirect to `https://atlanticautoservices.com`, keeping path and query | Pass | curl against the live domain |
| HSTS | Pass | Switched on after the certificate check; `max-age=31536000` live |
| Security headers | Pass | CSP, nosniff, frame DENY, referrer and permissions policies live |
| Session cookies | Pass | `__Host-` prefix, Secure, HttpOnly; admin `SameSite=Strict`; admin pages no-store and noindex |
| Code, config, dependencies, schema, dotfiles, deploy state, `error_log` | Pass | All 404 or 403 live |
| Errors safe | Pass | The 500s before the database was set up showed the site's error page with no technical details |
| Form protection | Pass | POST without a token gets 419 on the quote form and admin login |
| PHP version | Pass | 8.5.12 (minimum 8.2); the version header is removed by PR #9 |
| Cron jobs | Pass | mail, carrier, backup, retention, health installed in cPanel |
| Backup on the host | Pass | Created, owner-only, gzip verified, 12 tables; full restore rehearsed locally |
| Test enquiry email over SMTP | Pass | A test quote reached the shared inbox with the [Import Quote] subject |
| Privacy Policy and Terms of Use published | Pass (owner) | Full pages with "Last updated 18 November 2026", published on the owner's instruction (18 Nov 2026, PR #12) with the retention periods proposed in DEC-004. The client should still confirm DEC-004 and DEC-008 (Act 843 registration) |
| Contact details on the site | **Open (client)** | Phone, WhatsApp, email, office and hours to add in `config.php` |

EVD-006 stays PENDING until the client's contact details are on the site; everything technical has passed.

## Client items (not blocking the deploy)

- DEC-007 off-host backup destination.
- Sample cars removed (`php bin/seed-sample-cars.php --remove`) and real cars added, whenever the client's stock is ready. Until then the samples show a Sample badge and take no enquiries.
