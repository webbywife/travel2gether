# Security Review — Travel2gether

| | |
|---|---|
| Date | 2026-10-18 |
| Reviewer | Claude Code (Opus 5.5), for Lea Abarentos |
| Checklist | Generic Security Checklist (OWASP Top 10:2025, ASVS 5.0, WSTG) |
| Base commit | `fd4a3a5` + the fixes in the commit that adds this file |
| Risk tier | Elevated (see `SECURITY.md`) |
| Result | **PASS**: no open Critical or High findings |
| Functionality | Tests 200/200 before and after; 13 key pages and endpoints smoke-tested 200 |

## Summary
The app's core security is solid. Every trip route checks membership and record ownership on the server, every write is CSRF-protected, output is escaped, and there are no secrets in the repo. This pass found one vulnerable dependency (a ReDoS in `symfony/http-foundation`) and three Low issues; all four are fixed without changing behavior. **The #1 remaining item is a full Content-Security-Policy for scripts.** It isn't applied because the inline scripts first need nonces, and doing that blind could break pages.

## Findings — confirmed and fixed
| # | Severity | Standard(s) | File:Line | Issue | Fix |
|---|---|---|---|---|---|
| 1 | Medium | A03 · ASVS V15.2 · WSTG-CONF | `composer.lock` | `symfony/http-foundation` 8.1.6: CVE-2026-108082, ReDoS through the `Cache-Control` header | Patch update to 8.1.9; `composer audit` is clean |
| 2 | Low | A04/A09 · ASVS V13.3 · WSTG-CRYP | `TrippieAssistant.php:103`, `GenerateDayItinerary.php:141` | The Gemini API key was sent in the URL query, so it could end up in exception logs | Sent in the `x-goog-api-key` header instead |
| 3 | Low | A02 · ASVS V3.4 · WSTG-CONF-12 | `SecurityHeaders.php:25` | No Content-Security-Policy | Structural CSP added: `object-src 'none'; base-uri 'self'; frame-ancestors 'self'` |
| 4 | Low | A01/A05 · ASVS V2.2 · WSTG-INPV | `routes/web.php:95` | `/places/{placeId}` went unvalidated into an outbound API path | Route constraint `[A-Za-z0-9_-]{1,300}` |
| 5 | Low | Checklist §9 | repo root | No `SECURITY.md` / `SECURITY-REVIEW.md` | Both added |

Also fixed earlier the same day (redesign commit `fd4a3a5`): the JSON-LD on the home and destination pages was broken because Blade compiled `'@context'` as a directive. It's now written as `"\x40context"`.

**Habits that prevent these:** run `composer audit` before every deploy · send API keys only in headers · constrain every route parameter that is passed to another service · when adding a header, start with the CSP directives that can't break anything.

## Potential / needs Lea's OK (not applied, because they could change behavior)
1. **Full CSP for scripts and styles** (A02). This needs nonces on every inline `<script>` plus an allowlist (cdnjs, Google Fonts, Reverb). Roll it out first as `Content-Security-Policy-Report-Only`, then enforce.
2. **Alerting** (A09). Security events are logged but nobody is told. Once production mail works, send a daily email of `auth.lockout` / `auth.failed` spikes.
3. **MFA** (A07). There's no TOTP for password accounts. Google sign-in carries Google's own MFA. This would be a feature addition.
4. **Admin list** (§4). It lives in `.env` (`app.admin_emails`) rather than an auditable table. That's acceptable for a single-owner app; moving it would change how admin is granted.
5. **Funnel-event retention** (§7). 12 months is documented but no purge job is scheduled. Adding one deletes data, so it needs approval.
6. **Production config not re-checked in this pass** (SSH was not permitted). Carried over from the 2026-10-03 review and still to confirm: `APP_DEBUG=false`, a real mail driver, stale `.env.bak.*` files removed, the per-site Unix user, and FPM worker count.

**Assumptions:** production matches `main` after deploy, and the 2026-10-03 fixes (`aa4adeb`) are live.

## Pass/fail by standard
| Standard | Result |
|---|---|
| OWASP Top 10:2025 | PASS. A02 partial (no script CSP yet); A09 partial (logging, no alerting) |
| ASVS 5.0 | PASS at L1. V3.4 (CSP) and V16 (alerting) are open as improvements |
| WSTG | ATHZ pass (IDOR re-checked) · INPV pass · SESS pass · CONF pass with CSP partial · ERRH pass · CRYP pass · BUSL pass (trip cap, AI caps) · CLNT pass (`textContent`, escaped JSON-LD) |

## Done well
- Record-level authorization everywhere. Even the `.ics` export validates that each `stop:option` pair really belongs to the trip.
- Privacy-minded analytics: an HMAC-hashed session ID, no IPs, bots skipped. Security logs store email **hashes**, not addresses.
