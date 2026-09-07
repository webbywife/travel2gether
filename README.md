# Travel2gether

AI-generated, collaborative, shareable travel itineraries — grown from the hand-built
[Seoul 2026](https://webbywife.github.io/sk2026) prototype into a reusable platform.

See [`travel2gether-project-plan.md`](travel2gether-project-plan.md) for the full 9-phase roadmap.

## Status — Phase 1: schema extraction + data-driven render

The Seoul 2026 itinerary is no longer hand-coded HTML. It now lives in the database
in a reusable trip schema, and the itinerary page renders entirely from that data —
day tabs, tap-to-pick options, the live budget worksheet, live Open-Meteo weather,
and the outfit-photo lightbox.

| Table | Purpose |
|---|---|
| `trips` | slug, dates, party size, currency, map provider, geo anchor, boarding-pass segments, stat cards |
| `trip_days` | per-day title, weather + outfit block, "today's area" map, **required** `hiccups` |
| `stops` | time slot, title, description, cost tag, weather tag, map link, optional stop-level hiccup |
| `stop_options` | 3+ alternatives per slot — tier, `cost_min`/`cost_max` (feeds the budget), weather tag, sponsored flag |
| `budget_lines` | editable worksheet defaults, grouped by category |

## Local setup

```sh
cp .env.example .env
php artisan key:generate
mysql -uroot -e "CREATE DATABASE travel2gether"   # MySQL 8+
php artisan migrate --seed        # runs Seoul2026Seeder
php artisan serve
```

Open `http://localhost:8000` — it redirects to `/t/seoul-2026`.
Deep links: `/t/seoul-2026#3` opens Day 3, `#budget` opens the worksheet.

## Stack

Laravel 13 · MySQL · Blade + vanilla JS · Chart.js (CDN). No build step required
for the itinerary view. Deployed via Ploi to `travel2gether.webprvw.xyz`.

## Deploy (Ploi)

Ploi runs `deploy.sh` on push to `main`. It installs Composer deps, migrates, and
caches config/routes/views. The Seoul seeder is **not** run automatically — run it
once by hand after the first deploy:

```sh
php artisan migrate --force
php artisan db:seed --class=Seoul2026Seeder --force
```
