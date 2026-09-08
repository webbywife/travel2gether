# Trip images

Curated photos live here and are served straight from the web root
(`https://travel2gether.webprvw.xyz/img/trips/<slug>/<file>`). Ploi picks them up
on `git pull` — no upload step, no `storage:link`.

This is the Phase 8 "manual export" path. A real in-app upload UI (writing to
`storage/app/public`) comes later.

## Where to put Traveleyz photos

```
public/img/trips/seoul-2026/
```

## Naming convention (so the seeder can map them)

| Use | Filename |
|---|---|
| Page background | `bg.jpg` |
| Trip hero / OG image | `hero.jpg` |
| A day's outfit ideas | `day1-outfit-1.jpg`, `day1-outfit-2.jpg`, `day1-outfit-3.jpg` … |
| A day's "today's area" shot | `day1-area.jpg` |
| A stop photo | `stop-<short-name>.jpg` — e.g. `stop-sams-korean-bbq.jpg`, `stop-namsan-tower.jpg` |
| An option photo | `opt-<short-name>.jpg` |

Whatever you drop in, keep the names lowercase-with-dashes and tell me which
stop/day each one belongs to — I'll wire the paths into `Seoul2026Seeder`.

## Keep them web-sized

- Long edge ≤ 1600px
- JPEG, ~150–400 KB each
- These commit into the repo, so a tight set (20–40 photos) is ideal, not a dump.

## Getting your own posts off Instagram

You own `@traveleyz`, so: **Settings → Accounts Centre → Your information and
permissions → Download your information** (choose Photos, high quality). Or just
re-use the original files you posted from.
