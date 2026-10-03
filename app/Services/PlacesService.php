<?php

namespace App\Services;

use App\Models\Place;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * POI search + indexing.
 *
 * Uses the Google Places API (New) when GOOGLE_MAPS_API_KEY is set; otherwise
 * the free OpenStreetMap stack — Photon (komoot) for type-ahead search and
 * Overpass for "what's near this area" (AI grounding). No key, no billing.
 *
 * Search responses are cached (cheap repeats, polite to the free services);
 * Google place details are persisted into `places` and refreshed every 30 days.
 */
class PlacesService
{
    private const SEARCH_URL = 'https://places.googleapis.com/v1/places:searchText';
    private const DETAILS_URL = 'https://places.googleapis.com/v1/places/';

    private const PHOTON_URL = 'https://photon.komoot.io/api/';

    /** Public Overpass mirrors — tried in order; the main one often 504s under load. */
    private const OVERPASS_URLS = [
        'https://overpass-api.de/api/interpreter',
        'https://overpass.private.coffee/api/interpreter',
        'https://overpass.kumi.systems/api/interpreter',
    ];

    /** The free OSM services ask clients to identify themselves. */
    private const USER_AGENT = 'Travel2gether/1.0 (+https://travel2gether.net)';

    private ?string $key;

    public function __construct(?string $key = null)
    {
        $this->key = $key ?? config('services.google.maps_key');
    }

    /** Search always works: Google with a key, OpenStreetMap without. */
    public function enabled(): bool
    {
        return $this->usesGoogle() || (bool) config('services.places.osm', true);
    }

    public function usesGoogle(): bool
    {
        return filled($this->key);
    }

    public function provider(): string
    {
        return $this->usesGoogle() ? 'google' : 'osm';
    }

    /**
     * Text search, optionally biased to a lat/lon. Returns normalized rows.
     *
     * @return array<int, array<string, mixed>>
     */
    public function search(string $query, ?float $lat = null, ?float $lon = null, int $limit = 8): array
    {
        $query = trim($query);
        if (! $this->enabled() || $query === '') {
            return [];
        }

        $limit = max(1, min($limit, 20));

        if (! $this->usesGoogle()) {
            return $this->photonSearch($query, $lat, $lon, $limit);
        }

        $cacheKey = 'places:search:' . md5(mb_strtolower($query) . "|$lat|$lon|$limit");

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($query, $lat, $lon, $limit) {
            $body = ['textQuery' => $query, 'pageSize' => $limit];

            if ($lat !== null && $lon !== null) {
                $body['locationBias'] = ['circle' => [
                    'center' => ['latitude' => $lat, 'longitude' => $lon],
                    'radius' => 20000.0,
                ]];
            }

            $res = Http::withHeaders([
                'X-Goog-Api-Key' => $this->key,
                'X-Goog-FieldMask' => implode(',', [
                    'places.id', 'places.displayName', 'places.formattedAddress',
                    'places.location', 'places.types', 'places.rating',
                    'places.userRatingCount', 'places.priceLevel',
                ]),
            ])->timeout(8)->post(self::SEARCH_URL, $body);

            if (! $res->successful()) {
                return [];
            }

            return collect($res->json('places', []))
                ->map(fn (array $p) => $this->normalize($p))
                ->filter(fn (array $p) => $p['provider_id'] !== '')
                ->values()
                ->all();
        });
    }

    /**
     * Full details for one place, persisted and reused for 30 days.
     */
    public function details(string $providerId): ?Place
    {
        if (! $this->usesGoogle()) {
            // OSM results carry everything we use already; just return what's indexed.
            return Place::where('provider_id', $providerId)->first();
        }

        $place = Place::where(['provider' => 'google', 'provider_id' => $providerId])->first();

        if ($place?->details_fetched_at?->gt(now()->subDays(30))) {
            return $place;
        }

        if (! $this->enabled()) {
            return $place;
        }

        $res = Http::withHeaders([
            'X-Goog-Api-Key' => $this->key,
            'X-Goog-FieldMask' => implode(',', [
                'id', 'displayName', 'formattedAddress', 'location', 'types',
                'rating', 'userRatingCount', 'priceLevel',
                'internationalPhoneNumber', 'websiteUri',
            ]),
        ])->timeout(8)->get(self::DETAILS_URL . $providerId);

        if (! $res->successful()) {
            return $place;
        }

        $normalized = $this->normalize($res->json());

        return Place::updateOrCreate(
            ['provider' => 'google', 'provider_id' => $providerId],
            Arr::except($normalized, ['provider', 'provider_id']) + [
                'details_fetched_at' => now(),
                'raw' => $res->json(),
            ],
        );
    }

    /**
     * Real, named places around a point — feeds the AI so it uses actual
     * names. Google: a few text searches; OSM: one Overpass query.
     *
     * @param  array<int, string>  $interests
     * @return array<int, array<string, mixed>>
     */
    public function nearby(string $area, float $lat, float $lon, array $interests = [], int $limit = 16): array
    {
        if ($this->usesGoogle()) {
            $queries = array_slice(array_merge(
                ["things to do in {$area}", "restaurants in {$area}"],
                array_map(fn ($i) => "{$i} in {$area}", array_slice($interests, 0, 2)),
            ), 0, 4);

            return collect($queries)
                ->flatMap(fn ($q) => $this->search($q, $lat, $lon, 4))
                ->unique('provider_id')->take($limit)->values()->all();
        }

        if (! config('services.places.osm', true)) {
            return [];
        }

        $key = 'places:osm:nearby:' . round($lat, 3) . ',' . round($lon, 3);

        // The public Overpass servers are often overloaded. Grounding is a
        // nice-to-have (the AI drafts fine without it), so after a full
        // failure we stop trying for 10 minutes instead of slowing every draft.
        if (! Cache::has($key) && Cache::has('places:osm:overpass-down')) {
            return [];
        }

        $rows = Cache::remember($key, now()->addDays(7), function () use ($lat, $lon) {
            $r = 3000;
            $around = "(around:{$r},{$lat},{$lon})";
            // One statement per category, each with its own cap, so hundreds of
            // restaurants can't crowd out the sights.
            $q = '[out:json][timeout:20];'
                . "nwr{$around}[\"tourism\"~\"^(attraction|museum|gallery|viewpoint|zoo|aquarium|theme_park)$\"][\"name\"];out center tags 60;"
                . "nwr{$around}[\"historic\"~\"^(monument|castle|memorial|ruins|archaeological_site|palace)$\"][\"name\"];out center tags 30;"
                . "nwr{$around}[\"amenity\"=\"place_of_worship\"][\"name\"][\"wikidata\"];out center tags 20;"
                . "nwr{$around}[\"amenity\"~\"^(restaurant|cafe|marketplace|food_court)$\"][\"name\"];out center tags 60;"
                . "nwr{$around}[\"leisure\"~\"^(park|garden)$\"][\"name\"];out center tags 20;"
                . "nwr{$around}[\"shop\"~\"^(mall|department_store)$\"][\"name\"];out center tags 15;";

            $res = null;
            foreach (self::OVERPASS_URLS as $url) {
                $res = rescue(fn () => Http::withHeaders(['User-Agent' => self::USER_AGENT])
                    ->asForm()->timeout(15)->post($url, ['data' => $q]), null, false);
                if ($res?->successful() && is_array($res->json('elements'))) {
                    break;
                }
                $res = null;
            }

            if (! $res) {
                return null; // don't cache a failure for a week
            }

            return collect($res->json('elements', []))
                ->map(fn (array $e) => $this->normalizeOverpass($e))
                ->filter(fn ($p) => $p['name'] !== '')
                ->values()->all();
        });

        if ($rows === null) {
            Cache::forget($key);
            Cache::put('places:osm:overpass-down', true, now()->addMinutes(10));

            return [];
        }

        // A balanced mix: notable sights first (those with a Wikipedia/Wikidata
        // link), then food, parks and shopping.
        $by = collect($rows)->groupBy('kind');
        $pick = fn (string $kind, int $n) => ($by[$kind] ?? collect())->sortByDesc('notable')->take($n);

        return $pick('sight', 7)->concat($pick('food', 5))->concat($pick('park', 2))->concat($pick('shop', 2))
            ->unique('provider_id')->take($limit)
            ->map(fn ($p) => \Illuminate\Support\Arr::except($p, ['kind', 'notable']))
            ->values()->all();
    }

    /** @return array<int, array<string, mixed>> */
    private function photonSearch(string $query, ?float $lat, ?float $lon, int $limit): array
    {
        $cacheKey = 'places:osm:search:' . md5(mb_strtolower($query) . "|$lat|$lon|$limit");

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($query, $lat, $lon, $limit) {
            $params = ['q' => $query, 'limit' => $limit, 'lang' => 'en'];
            if ($lat !== null && $lon !== null) {
                $params += ['lat' => $lat, 'lon' => $lon];
            }

            $res = rescue(fn () => Http::withHeaders(['User-Agent' => self::USER_AGENT])
                ->timeout(8)->get(self::PHOTON_URL, $params), null, false);

            if (! $res?->successful()) {
                return [];
            }

            return collect($res->json('features', []))
                ->map(fn (array $f) => $this->normalizePhoton($f))
                ->filter(fn (array $p) => $p['provider_id'] !== '' && $p['name'] !== '')
                ->values()
                ->all();
        });
    }

    /** @return array<string, mixed> */
    private function normalizePhoton(array $f): array
    {
        $p = $f['properties'] ?? [];
        $street = trim(($p['street'] ?? '') . ' ' . ($p['housenumber'] ?? ''));
        $address = collect([$street, $p['district'] ?? null, $p['city'] ?? null, $p['state'] ?? null, $p['country'] ?? null])
            ->filter()->unique()->implode(', ');

        return [
            'provider' => 'osm',
            'provider_id' => isset($p['osm_id'], $p['osm_type']) ? $p['osm_type'] . $p['osm_id'] : '',
            'name' => (string) ($p['name'] ?? ''),
            'formatted_address' => $address ?: null,
            'lat' => data_get($f, 'geometry.coordinates.1'),
            'lon' => data_get($f, 'geometry.coordinates.0'),
            'types' => array_values(array_filter([($p['osm_key'] ?? '') . ':' . ($p['osm_value'] ?? '')], fn ($t) => $t !== ':')),
            'rating' => null,
            'rating_count' => null,
            'price_level' => null,
        ];
    }

    /** @return array<string, mixed> */
    private function normalizeOverpass(array $e): array
    {
        $t = $e['tags'] ?? [];
        $kind = match (true) {
            ($t['amenity'] ?? null) === 'place_of_worship' => 'sight',
            isset($t['amenity']) => 'food',
            isset($t['leisure']) => 'park',
            isset($t['shop']) => 'shop',
            default => 'sight',
        };
        $address = collect([
            trim(($t['addr:street'] ?? '') . ' ' . ($t['addr:housenumber'] ?? '')),
            $t['addr:city'] ?? null,
        ])->filter()->implode(', ');

        return [
            'provider' => 'osm',
            'provider_id' => strtoupper(substr($e['type'] ?? 'n', 0, 1)) . ($e['id'] ?? ''),
            'name' => (string) ($t['name:en'] ?? $t['name'] ?? ''),
            'formatted_address' => $address ?: null,
            'lat' => $e['lat'] ?? data_get($e, 'center.lat'),
            'lon' => $e['lon'] ?? data_get($e, 'center.lon'),
            'types' => [$kind],
            'kind' => $kind,
            'notable' => isset($t['wikidata']) || isset($t['wikipedia']),
        ];
    }

    /**
     * @param  array<string, mixed>  $p
     * @return array<string, mixed>
     */
    private function normalize(array $p): array
    {
        return [
            'provider' => 'google',
            'provider_id' => (string) ($p['id'] ?? ''),
            'name' => (string) data_get($p, 'displayName.text', ''),
            'formatted_address' => $p['formattedAddress'] ?? null,
            'lat' => data_get($p, 'location.latitude'),
            'lon' => data_get($p, 'location.longitude'),
            'types' => $p['types'] ?? [],
            'rating' => $p['rating'] ?? null,
            'rating_count' => $p['userRatingCount'] ?? null,
            'price_level' => $p['priceLevel'] ?? null,
            'phone' => $p['internationalPhoneNumber'] ?? null,
            'website' => $p['websiteUri'] ?? null,
        ];
    }
}
