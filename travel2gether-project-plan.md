# Travel2gether — Project Plan
*AI-generated, collaborative, shareable travel itineraries — built from the Seoul 2026 prototype*

---

## 1. Brand

**Working name: Travel2gether**
Alternatives considered: Trippers, Tripsmith, Tripthread, CoRoute, Waypoint, Jaunt, Packmates, Wanderling.

Rationale: Makes the core value proposition explicit right in the name — planning and traveling *together* — which directly reflects the collaborator/shared-picks features (Phase 2) and the community gallery (Phase 9). The "2" gives it a distinctive, easy-to-remember spelling for a domain (travel2gether.com / .app).

---

## 2. What we're building

The existing Seoul 2026 itinerary (`webbywife.github.io/sk2026`) is a hand-built, single-trip prototype with:
- Day-by-day structure with tap-to-pick options for meals, shopping, and activities
- Cost tags, live-updating budget worksheet
- "Today's area" maps, outfit/weather notes, and a "Possible hiccups" section per day

The goal is to turn this pattern into a reusable, collaborative, multi-trip **platform** — not just a nicer version of one page.

---

## 3. Core data schema (foundation for everything else)

Every stop in a trip should carry:

| Field | Purpose |
|---|---|
| `time`, `title`, `description` | Base itinerary content |
| `options[]` | 3+ alternatives per slot (budget/mid/splurge etc.) |
| `cost_range` | Per option, used for the live budget worksheet |
| `weather_tag` | indoor / covered / outdoor — powers auto-swap logic |
| `map_provider` | google / naver / kakao — routes to whichever service works in that country |
| `hiccup` | Required field — forces every trip to document failure modes |
| `photos[]` | References to hosted images (see Phase 5) |

---

## 4. AI trip generation & pricing model

**Core mechanic**: user describes a trip (destination, dates, interests, budget, group size) and the AI generates a full first draft — day-by-day structure, options per slot, cost tags, weather-aware notes, and hiccups — in the same format as the Seoul prototype, ready for the group to then tap through, adjust, and collaborate on.

**Pricing**
- **First trip: free.** One full AI-generated trip per account, no card required — this is the "aha moment" hook, letting someone experience the full generated itinerary before paying anything.
- **Second trip onward: paid.** Requires a subscription (not pay-per-trip), which bundles naturally with the paid tier already planned: unlimited collaborators, the weather auto-swap/cost-delta engine, priority weather alerts, and analytics.
- *Open decision*: whether unlimited *regeneration* of the free trip is allowed, or the free trip locks after first generation to keep the upgrade incentive clear. Recommend locking it — unlimited free regeneration weakens the reason to pay.

**Why subscription over pay-per-trip**: a subscription matches the collaborative nature of the product — groups planning multiple trips together (like recurring friend trips or a couple planning several getaways a year) get more value from an unlimited plan than a one-off purchase, and it ties revenue to ongoing engagement rather than a single transaction.

---

## 5. Phased roadmap

### Phase 1 — Extract the schema (1–2 weeks)
Convert the hand-built Seoul HTML into the reusable JSON/YAML structure above, so any new trip can be generated from data instead of hand-coded per city.

### Phase 2 — Accounts, collaborators & synced picks (2–3 weeks)
This is the highest-leverage phase — it unlocks sharing, collaboration, *and* analytics at once.
- User accounts (email or social login)
- Shared backend (Supabase/Firebase) so picks sync live across everyone's devices, replacing local-only storage
- **Roles**: Owner (invite/remove collaborators, lock decisions, set public/private), Editor (vote, propose stops, comment, edit budget), Viewer (read-only link)
- Invite via shareable link with role attached, or direct email — same mental model as Google Docs sharing
- Attribution on picks ("Marco picked Sam's Korean BBQ") for transparency

### Phase 3 — Weather-driven adjustments (this is the differentiator)
- Pull live forecast a few days out per trip
- If rain probability or heat index crosses a threshold, flag affected outdoor stops automatically
- Surface the pre-built indoor/covered alternative already in the options list — one tap, not a re-plan
- Show the **cost delta** of any swap (+/- $) and roll it live into the budget worksheet total

### Phase 4 — Deeper option sets
- Dining: budget / mid / splurge, plus at least one indoor-AC and one rain-friendly pick per slot
- Shopping: mall (indoor) / street market (cheap, exposed) / flagship boutique (splurge, indoor)
- Every option weather-tagged so Phase 3's auto-swap always has somewhere to send people

### Phase 5 — Maps integration
- Google Maps as the default pin/link everywhere
- Naver Map or Kakao Map override for Korea (and similar per-country overrides elsewhere, e.g. Japan) — Google's routing/business data is notably weaker in Korea due to local data export restrictions
- `map_provider` field per trip or per stop controls which service the "Map ↗" link opens

### Phase 6 — Recommendation analytics
- Aggregate pick rates per option across all users at a given time slot/city ("83% picked Sam's Korean BBQ")
- Surface a "Popular pick" badge on the highest-picked option
- Longer-term: city/season trends (most-picked shopping district by month, most-flagged hiccups) — useful data for future travelers and a potential B2B asset
- Aggregate and anonymize before display; data sharing opt-in at signup

### Phase 7 — Shareable links & remix
- Public trip URL per trip with Open Graph tags (clean preview when pasted in group chats)
- "Duplicate this itinerary" — fork someone else's trip as a starting template
- Comments/reactions on individual stop options for group decision-making

### Phase 8 — Photo pipeline
- Instagram cannot be scraped automatically (blocks bots) — two viable paths:
  - **Manual export** (fastest to ship): upload curated photos to a hosted folder/CDN (GitHub, Cloudinary) and reference them in the trip data
  - **Instagram Graph API** (for later): if the account is Business/Creator, pull your own posts programmatically — worth it once publishing trips at volume

### Phase 9 — Community gallery
Public, opt-in gallery of shared itineraries others can browse and remix — this is where the "sharing" half of the brand really pays off.

---

## 6. Monetization

| Model | Description |
|---|---|
| **Affiliate commissions** | Route existing outbound links (restaurants, hotels, activities, transport) through affiliate programs (Booking.com, Klook, GetYourGuide, Trip.com, Kakao T) — near-zero added friction |
| **Freemium subscription** | Free: one active trip, limited collaborators, basic weather. Paid: unlimited collaborators, auto-swap/cost-delta engine, priority weather alerts, analytics |
| **Creator/template marketplace** | Sell curated itinerary templates (e.g. "Seoul in 5 Days") — a direct path to monetize existing travel expertise and an Instagram following |
| **Sponsored picks** | Local businesses pay for a clearly labeled featured option in relevant slots — must stay transparently marked to preserve trust in the "why it's worth it" reasoning |
| **B2B / white-label** | License to conference organizers, tourism boards, or travel agencies for branded attendee/client itineraries — the Seoul trip itself, built around a conference, is a live example of this use case |

---

## 7. Value created

- **Travel groups**: replaces group-chat decision chaos with structured, evidence-based choices and a visible record of why a pick was made
- **First-time visitors to a country**: local-knowledge details (correct subway line, which market stall closes early, Naver vs. Google Maps) reduce the anxiety of navigating somewhere unfamiliar
- **Budget-conscious travelers**: live cost-delta and budget worksheet prevent post-trip financial surprises
- **Creators**: travel knowledge and photography become a reusable, sellable asset instead of a one-off post that disappears in a feed
- **Local businesses**: featured placement drives travelers who are actively deciding *right now*, not passive ad impressions

---

## 8. Suggested phase order (build sequence)

1. Schema extraction
2. Accounts + collaborators + synced picks (do together — unlocks sharing and analytics)
3. Weather → auto-swap → cost delta engine
4. Deeper dining/shopping option sets
5. Maps routing (Google default, Naver/Kakao override)
6. Recommendation analytics
7. Shareable links, duplicate/remix, comments
8. Photo pipeline
9. Community gallery
