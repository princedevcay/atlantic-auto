# Atlantic Auto and Shipping Services

Website for a Ghanaian company importing vehicles from the USA: import quote requests, vehicle update requests, team-managed tracking pages with a carrier feed, and a staff admin.

## Stack

- PHP and MySQL, no framework required on the server
- Styling: Tailwind CSS (not Bootstrap). Compiled with the Tailwind CLI during the GitHub Actions build, so only the finished, minified CSS file is uploaded. No Play CDN script in production. Colours, radii and fonts come from `.opskeep/project-gate/tokens.json` as the Tailwind theme.
- Hosting: Namecheap shared hosting (cPanel)
- Deploys: GitHub Actions uploads the site over FTP (FTPS where the host allows it) on push to `main`

## Secrets

Nothing secret is committed. This repo is public.

- Database, mail and carrier settings live in `config.php` on the server, outside `public_html`. `config.example.php` shows the setting names only.
- FTP login details live only in the repository's GitHub Actions secrets (`FTP_SERVER`, `FTP_USERNAME`, `FTP_PASSWORD`), added by the owner in GitHub under Settings, Secrets and variables, Actions. Use a dedicated FTP account limited to this site's folder.

## Design

The approved design lives in `.opskeep/project-gate/`. Read `DESIGN.md` before changing any UI.

## Records

- [ARCHITECTURE.md](ARCHITECTURE.md): components, server layout, data flows, decisions
- [SECURITY.md](SECURITY.md): security baseline summary and how to report a vulnerability
- [GUI.md](GUI.md): interface handoff, capability status, contracts
- [security-baseline.yaml](security-baseline.yaml): the authoritative security record

## Local development

Needs PHP 8.2+, Composer, Node 22 and MySQL or MariaDB.

```bash
composer install && npm ci --ignore-scripts && npm run build
```

1. Copy `config.example.php` to `private/config.php` (gitignored) and set `app.env` to `local`, a database, and `paths` under `private/`.
2. `php bin/migrate.php`
3. `php bin/create-admin.php "Your Name" you@example.com` (prints a temporary password once)
4. `php -S localhost:8080 -t public scripts/dev-router.php`

Tests: `vendor/bin/phpunit` (set `BASE_URL` and `ATLANTIC_CONFIG` to also run the end-to-end access tests against a running server), `vendor/bin/phpstan analyse`, `bash scripts/check-patterns.sh`.

## Server setup (Namecheap cPanel)

Domain: **atlanticautoservices.com**, folder `~/atlanticautoservices.com` (created by cPanel).

```
~/atlanticautoservices.com/    deployed by GitHub Actions over FTPS (the FTP account's home)
  .htaccess                    sends every request into public/, 404s app code
  public/                      the only web-reachable folder (best: set it as the document root)
  app/ bin/ database/ vendor/
~/atlantic-auto-private/       never deployed or overwritten
  config.php                   from config.example.php
  storage/ backups/
```

1. **Document root (recommended):** in cPanel, Domains, set the document root of atlanticautoservices.com to `atlanticautoservices.com/public`. The root `.htaccess` also protects the code if you leave it as is.
2. **PHP 8.2 or later** for the domain (cPanel, Select PHP Version), with `pdo_mysql`, `gd`, `fileinfo`, `openssl`, `curl`.
3. **SSL:** AutoSSL certificate active for the domain and www.
4. **Database:** create a MySQL database and a user with privileges on that database only.
5. **FTP account:** cPanel, FTP Accounts, create a dedicated account whose directory is `atlanticautoservices.com` (not the account root). Use a long generated password.
6. **GitHub secrets:** add them yourself in GitHub, Settings, Environments, production (or run `gh secret set NAME --env production` and paste when prompted): `FTP_SERVER` (the server hostname from cPanel), `FTP_USERNAME`, `FTP_PASSWORD`.
7. **Config:** in cPanel File Manager or Terminal, create `~/atlantic-auto-private/config.php` from `config.example.php` with the real database, mail, contact, `app.url` `https://atlanticautoservices.com` and a new `app.key`; `chmod 600` it.
8. **After the first deploy** (cPanel Terminal): `php ~/atlanticautoservices.com/bin/migrate.php`, then `php ~/atlanticautoservices.com/bin/create-admin.php "Name" email`.
9. **Cron jobs:** see the header of `bin/cron.php`, with paths under `~/atlanticautoservices.com`.
10. **Sample cars:** `php bin/seed-sample-cars.php` adds five sample listings (Sample badge, reference prices, no enquiries). **Before launch run `php bin/seed-sample-cars.php --remove`** and add the client's real cars in the admin.

Deploys run on every push to `main`, wait for owner approval in the `production` environment, and stop at the security baseline check until the pre-launch gates have passing evidence.

## Status

Project Gate approved (reopened on 17 Nov 2026 for the new landing page mockup and car brand logos, PG-028 and PG-029, awaiting re-approval). Security baseline 0.2.0 approved (18 Nov 2026). Customer tracking is switched off (`features.customer_tracking`). The site and admin are built and tested locally; production deploys stay blocked until the pre-launch gates have passing evidence.
