# Security design — Travel2gether

Personal project (owner: Lea Abarentos). This repo is **public**: this file holds design decisions only, never secrets, server details or unfixed-vulnerability specifics. To report a vulnerability, email the owner privately; please don't open a public issue.

## Risk tier
**Elevated.** The app holds account data and travel plans (dates, flights, hotels) that show where someone will be and when. It holds no payment, health or government-ID data.

## Data we keep, and why
| Data | Where | Why we need it |
|---|---|---|
| Name, email | `users` | Sign-in, showing who made a pick, invites |
| Password hash (bcrypt) or Google account ID | `users` | Sign-in. We never store plain passwords or Google tokens. |
| Email-verified time, remember token | `users` | Account verification, "stay logged in" |
| Subscription flag | `users` | Free-tier trip limit |
| Trip title, destination, dates, party size, currency, flights, hotel, interests | `trips`, `trip_days`, `trip_segments` | The itinerary itself |
| Stops, options, budget lines | `stops`, `stop_options`, `budget_lines` | The itinerary itself |
| Membership and role, invites, removals | `trip_user`, `trip_invites`, `trip_removals` | Sharing (owner / editor / viewer) |
| Picks and who made them | `trip_picks` | Group decisions, with attribution |
| Place up/down votes | `place_recommendations` | Recommendations |
| Funnel events: day, source, hashed session, mobile flag | `visit_events` | Launch analytics. Anonymous: no IP, no user ID. |
| Security events: hashed email, user ID, IP | `storage/logs/security.log`, kept 90 days | Detecting attacks on accounts |

## Access model
- **Roles per trip:** owner, editor and viewer, stored in the `trip_user` pivot. Policies in `TripPolicy` and `Trip::canView` / `canPick`. Every trip route checks on the server, and record IDs (day, stop, option, member) are re-checked against the trip in the URL.
- **Public trips:** only the seeded samples (`is_public`, `created_by = null`). Other trips are visible to members only and return 404 to everyone else.
- **Admin:** the `admin` gate (analytics only), defined by a configured email that has to be a verified account. Admin can't read other people's private trips.
- **Who can reach it:** a public web app, by design. No shared or generic accounts.

## Run-as and secrets
- PHP-FPM runs as the deploy user. All secrets (`APP_KEY`, DB, `ANTHROPIC_API_KEY`, `GEMINI_API_KEY`, Google OAuth, mail) live in the server `.env`, which is not web-reachable. Only the owner reads it (over SSH).
- External calls are HTTPS only: Anthropic, Gemini (key sent in a header, never in the URL), Google OAuth, Open-Meteo, Photon/Overpass, Pexels.

## Protections in place
CSRF on every write · rate limits on login, register, reset, OAuth, AI and places · minimum password length 12 · email verification (toggled by env) · Blade auto-escaping, and `textContent` in client JS · security headers (nosniff, SAMEORIGIN, Referrer-Policy, Permissions-Policy, HSTS, structural CSP) · JSON-LD output with `JSON_HEX_TAG` · daily AI cap · security event log.

## Backup and restore
- **Backup:** a nightly `mysqldump` cron (19:30 UTC) to a directory outside the web root on the server, 14 days kept. DB credentials sit in a mode-600 option file.
- **Restore:** `gunzip -c <backup>.sql.gz | mysql <db>`, then `php artisan migrate --force` and `php artisan optimize:clear`. Test a restore into a scratch DB every quarter.

## Retention
- **Accounts and trips:** kept until the user deletes them (deleting a trip removes its days, stops, picks and invites).
- **Security log:** 90 days.
- **Funnel events:** anonymous; delete after 12 months.
- **Backups:** 14 days.

## Kill switch and revoking access
- **Whole app:** `php artisan down --secret=<token>` (maintenance page; the owner can still get in through the secret URL). Bring it back with `php artisan up`.
- **AI only:** remove `ANTHROPIC_API_KEY` / `GEMINI_API_KEY` from `.env`, then run `php artisan config:cache`.
- **Revoke admin:** change the admin email in `.env`, then `config:cache`.
- **Revoke a trip member:** the owner removes them in the Share panel. A removal row blocks rejoining through old invite links.

## Incident contacts
Owner: Lea Abarentos (via the site's contact email). For a personal-data breach, assess and notify within 72 hours, as RA 10173 / NPC Circular 16-03 require.
