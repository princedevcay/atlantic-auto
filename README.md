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

## Status

Project Gate approved and sealed. Security baseline 0.1.0 approved on 17 Nov 2026 (development and production on Namecheap cPanel). Implementation can start; production deploys wait for the CI and pre-launch gates. No application code exists yet, so there are no setup, test or deploy commands.
