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

- Code deploys to `~/atlantic-auto/` over FTPS; point the domain's document root at `~/atlantic-auto/public`.
- `~/atlantic-auto-private/config.php` from `config.example.php`, with `storage` and `backups` paths in that folder. Deploys never touch it.
- In cPanel Terminal: `php ~/atlantic-auto/bin/migrate.php`, then `php ~/atlantic-auto/bin/create-admin.php "Name" email`.
- Cron jobs: see the header of `bin/cron.php`.
- Client photos go in `public/assets/img/site/` (names in `brand/LOGOS.md`).

## Status

Project Gate approved (reopened on 17 Nov 2026 for the new landing page mockup and car brand logos, PG-028 and PG-029, awaiting re-approval). Security baseline 0.1.0 approved. The site and admin are built and tested locally; production deploys stay blocked until the pre-launch gates have passing evidence.
