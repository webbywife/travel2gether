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
    /** Search filters, Booking-style: "area" = districts/neighbourhoods/towns, "hotel" = places to stay. */
    private const KIND_FILTERS = [
        'area' => ['layer' => ['district', 'locality', 'city', 'county']],
        'hotel' => ['osm_tag' => ['tourism:hotel', 'tourism:hostel', 'tourism:guest_house', 'tourism:motel', 'tourism:apartment']],
        'destination' => ['layer' => ['city', 'state', 'country', 'locality', 'district', 'county']],
    ];

    /** How far from the trip a result may be before it's dropped (km). */
    private const KIND_RADIUS_KM = ['area' => 400, 'hotel' => 150];

    public function search(string $query, ?float $lat = null, ?float $lon = null, int $limit = 8, ?string $kind = null): array
    {
        $kind = isset(self::KIND_FILTERS[$kind]) ? $kind : null;
        $query = trim($query);
        if (! $this->enabled() || $query === '') {
            return [];
        }

        $limit = max(1, min($limit, 20));

        if (! $this->usesGoogle()) {
            return $this->photonSearch($query, $lat, $lon, $limit, $kind);
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
    private function photonSearch(string $query, ?float $lat, ?float $lon, int $limit, ?string $kind = null): array
    {
        $cacheKey = 'places:osm:search:' . md5(mb_strtolower($query) . "|$lat|$lon|$limit|$kind");

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($query, $lat, $lon, $limit, $kind) {
            // Ask for extra so the distance filter still leaves enough.
            $query_ = ['q' => $query, 'limit' => $kind ? min(20, $limit * 2) : $limit, 'lang' => 'en'];
            if ($lat !== null && $lon !== null) {
                $query_ += ['lat' => $lat, 'lon' => $lon];
            }
            // Photon takes repeated keys (layer=…&layer=…), so build the query string by hand.
            $qs = http_build_query($query_);
            foreach (self::KIND_FILTERS[$kind] ?? [] as $key => $values) {
                foreach ($values as $v) {
                    $qs .= '&' . $key . '=' . rawurlencode($v);
                }
            }

            $res = rescue(fn () => Http::withHeaders(['User-Agent' => self::USER_AGENT])
                ->timeout(8)->get(self::PHOTON_URL . '?' . $qs), null, false);

            if (! $res?->successful()) {
                return [];
            }

            $rows = collect($res->json('features', []))
                ->map(fn (array $f) => $this->normalizePhoton($f))
                ->filter(fn (array $p) => $p['provider_id'] !== '' && $p['name'] !== '' && $p['lat'] !== null);

            // Drop far-away namesakes (an "Ibis" in Brazil when planning Tokyo).
            if (isset(self::KIND_RADIUS_KM[$kind]) && $lat !== null && $lon !== null) {
                $rows = $rows->filter(fn ($p) => self::km($lat, $lon, $p['lat'], $p['lon']) <= self::KIND_RADIUS_KM[$kind]);
            }

            // One entry per name+place (OSM often has a node and a building for the same hotel).
            return $rows->unique(fn ($p) => mb_strtolower($p['name'] . '|' . $p['formatted_address']))
                ->take($limit)->values()->all();
        });
    }

    /** Photon queries per group: [query words, OSM tags, max km, how many to keep, map zoom for the location bias]. */
    private const EXPLORE = [
        'hotels' => [['hotel', 'hostel', 'inn'], ['tourism:hotel', 'tourism:hostel', 'tourism:guest_house', 'tourism:apartment'], 4, 10, 15],
        'landmarks' => [['museum', 'park', 'temple', 'tower', 'square'],
            ['tourism:museum', 'tourism:attraction', 'tourism:viewpoint', 'tourism:gallery', 'leisure:park', 'leisure:garden',
             'historic:monument', 'historic:castle', 'historic:memorial', 'amenity:place_of_worship', 'man_made:tower', 'man_made:bridge',
             'amenity:marketplace', 'place:square'], 6, 10, 14],
    ];

    /**
     * "Near this area": airports within reach, hotels close by, and landmarks —
     * grouped, deduped and sorted by distance. Uses Photon (fast, free) with
     * several targeted queries run in parallel.
     *
     * @return array{airports: array, hotels: array, landmarks: array}|null  null = lookup unavailable
     */
    public function explore(float $lat, float $lon): ?array
    {
        if (! config('services.places.osm', true)) {
            return null;
        }
        $key = 'places:osm:explore6:' . round($lat, 3) . ',' . round($lon, 3);

        $result = Cache::get($key);
        if ($result === null) {
            $result = $this->exploreFromPhoton($lat, $lon);
            if ($result !== null) {
                // a lookup where some queries failed is kept briefly, so the gap isn't remembered for a week
                Cache::put($key, $result, empty($result['partial']) ? now()->addDays(7) : now()->addMinutes(10));
            }
        }

        if ($result === null) {
            $result = ['hotels' => [], 'landmarks' => [], 'areas' => [], 'partial' => true]; // airports still work offline
        }

        return ['airports' => self::nearestAirports($lat, $lon)] + $result;
    }

    /** Hotels + landmarks around a point from parallel Photon queries; null when Photon is unreachable. */
    private function exploreFromPhoton(float $lat, float $lon): ?array
    {
        $jobs = [];
        foreach (self::EXPLORE as $group => [$words, $tags, , , $zoom]) {
            foreach ($words as $w) {
                $qs = http_build_query(['q' => $w, 'limit' => 25, 'lang' => 'en', 'lat' => $lat, 'lon' => $lon,
                    'location_bias_scale' => 0.1, 'zoom' => $zoom]);
                foreach ($tags as $t) {
                    $qs .= '&osm_tag=' . rawurlencode($t);
                }
                $jobs[] = [$group, self::PHOTON_URL . '?' . $qs];
            }
        }

        $responses = rescue(fn () => Http::pool(fn ($pool) => array_map(
            fn ($j) => $pool->withHeaders(['User-Agent' => self::USER_AGENT])->timeout(8)->get($j[1]), $jobs)), [], false);

        $out = ['hotels' => [], 'landmarks' => []];
        $ok = 0;
        $failed = false;
        foreach ($jobs as $i => [$group]) {
            $res = $responses[$i] ?? null;
            if (! $res instanceof \Illuminate\Http\Client\Response || ! $res->successful()) {
                $failed = true;
                continue;
            }
            $ok++;
            foreach ($res->json('features', []) as $f) {
                $p = $this->normalizePhoton($f);
                if ($p['name'] === '' || $p['lat'] === null) {
                    continue;
                }
                $p['km'] = round(self::km($lat, $lon, (float) $p['lat'], (float) $p['lon']), 1);
                $out[$group][] = $p;
            }
        }
        if ($ok === 0) {
            return null;
        }

        $areas = self::stayAreas($out['hotels'], $lat, $lon);

        foreach (self::EXPLORE as $group => [, , $maxKm, $keep, ]) {
            $out[$group] = collect($out[$group])
                ->filter(fn ($p) => $p['km'] <= $maxKm)
                // generic names ("Airport", "Park") and heliports aren't useful suggestions
                ->reject(fn ($p) => str_word_count($p['name']) < 2 && ! preg_match('/\d/', $p['name']))
                ->reject(fn ($p) => $group === 'airports' && stripos($p['name'], 'heliport') !== false)
                ->unique(fn ($p) => mb_strtolower($p['name']))
                ->sortBy('km')->take($keep)->values()->all();
        }

        $out['areas'] = $areas;

        return $failed ? $out + ['partial' => true] : $out;
    }

    /**
     * Where travellers stay: the districts the nearby hotels cluster in (most
     * hotels first), each placed at the middle of its hotels — e.g. Chuo Ward
     * and Kita Ward for Sapporo.
     *
     * @param  array<int, array<string, mixed>>  $hotels
     * @return array<int, array<string, mixed>>
     */
    private static function stayAreas(array $hotels, float $lat, float $lon, float $maxKm = 8, int $keep = 5): array
    {
        return collect($hotels)
            ->unique(fn ($h) => $h['provider_id'] ?: mb_strtolower($h['name']))
            ->filter(fn ($h) => filled($h['district'] ?? null) && $h['km'] <= $maxKm)
            ->groupBy(fn ($h) => $h['district'])
            ->filter(fn ($group) => $group->count() >= 2)
            ->map(function ($group, $name) use ($lat, $lon) {
                $cLat = round($group->avg('lat'), 5);
                $cLon = round($group->avg('lon'), 5);

                return ['name' => (string) $name, 'lat' => $cLat, 'lon' => $cLon,
                    'km' => round(self::km($lat, $lon, $cLat, $cLon), 1), 'hotels' => $group->count()];
            })
            ->sortByDesc('hotels')->take($keep)->values()->all();
    }

    /**
     * Airports with scheduled flights near a point, from the bundled OurAirports
     * list (public domain) — offline, instant, and names in English.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function nearestAirports(float $lat, float $lon, float $maxKm = 150, int $keep = 4): array
    {
        $all = once(fn () => json_decode((string) file_get_contents(database_path('data/airports.json')), true)['airports'] ?? []);

        return collect($all)
            ->map(fn ($a) => ['a' => $a, 'km' => self::km($lat, $lon, $a[4], $a[5])])
            ->filter(fn ($x) => $x['km'] <= $maxKm)
            ->sortBy('km')->take($keep)
            ->map(fn ($x) => [
                'provider' => 'ourairports',
                'provider_id' => $x['a'][0],
                'name' => "{$x['a'][1]} ({$x['a'][0]})",
                'category' => 'Airport',
                'formatted_address' => trim(($x['a'][2] ?: '') . ', ' . $x['a'][3], ', '),
                'lat' => $x['a'][4],
                'lon' => $x['a'][5],
                'km' => round($x['km'], 1),
                'major' => (bool) ($x['a'][6] ?? false), // OurAirports "large_airport" — where international flights land
            ])->values()->all();
    }

    /**
     * Safety and supplies near a point, from OpenStreetMap (free): the nearest hospitals/clinics, police stations,
     * pharmacies and groceries. A full answer is cached a week per ~100 m cell. When every Overpass mirror is down
     * we fall back to Photon for hospitals and police only (pharmacy and grocery come back null = "not available"),
     * and that partial answer is only cached for a few minutes.
     *
     * @return array{groups: array<string, array<int, array{name:string, km:float, map_url:string}>|null>, partial: bool}
     */
    public function essentials(float $lat, float $lon): array
    {
        $lat = round($lat, 3);
        $lon = round($lon, 3);
        $key = "places:osm:essentials:{$lat}:{$lon}";

        if ($full = Cache::get($key)) {
            return ['groups' => $full, 'partial' => false];
        }
        $full = $this->essentialsFromOverpass($lat, $lon) ?? $this->essentialsFromGeoapify($lat, $lon);
        if ($full) {
            Cache::put($key, $full, now()->addDays(7));
            Cache::put($key . ':last', $full, now()->addDays(90)); // outlives the week, so an outage can still be answered

            return ['groups' => $full, 'partial' => false];
        }
        if ($stale = Cache::get($key . ':last')) {
            return ['groups' => $stale, 'partial' => false];
        }

        $partialKey = $key . ':partial';
        $groups = Cache::remember($partialKey, now()->addMinutes(5), fn () => [
            'hospital' => $this->essentialsFromPhoton($lat, $lon, 'hospital', 'amenity:hospital'),
            'police' => $this->essentialsFromPhoton($lat, $lon, 'police', 'amenity:police'),
            'pharmacy' => null,
            'grocery' => null,
        ]);

        return ['groups' => $groups, 'partial' => true];
    }

    /** @return array<string, array<int, array{name:string, km:float, map_url:string}>>|null null when every mirror failed */
    private function essentialsFromOverpass(float $lat, float $lon): ?array
    {
        $around = "(around:6000,{$lat},{$lon})";
        $q = '[out:json][timeout:20];'
            . "nwr{$around}[\"amenity\"~\"^(hospital|clinic)$\"][\"name\"];out center tags 40;"
            . "nwr{$around}[\"amenity\"=\"police\"][\"name\"];out center tags 20;"
            . "nwr{$around}[\"amenity\"=\"pharmacy\"][\"name\"];out center tags 30;"
            . "nwr{$around}[\"shop\"~\"^(supermarket|convenience|grocery)$\"][\"name\"];out center tags 40;";

        // A person is waiting on this: keep the whole lookup well under PHP's 30 s limit (7 s per mirror, 20 s total).
        $res = null;
        $deadline = microtime(true) + 20;
        foreach (self::OVERPASS_URLS as $url) {
            if (microtime(true) > $deadline - 3) {
                break;
            }
            $res = rescue(fn () => Http::withHeaders(['User-Agent' => self::USER_AGENT])
                ->asForm()->connectTimeout(4)->timeout(7)->post($url, ['data' => $q]), null, false);
            if ($res?->successful() && is_array($res->json('elements'))) {
                break;
            }
            $res = null;
        }
        if (! $res) {
            return null;
        }

        $groups = ['hospital' => [], 'police' => [], 'pharmacy' => [], 'grocery' => []];
        foreach ($res->json('elements', []) as $e) {
            $t = $e['tags'] ?? [];
            $plat = $e['lat'] ?? ($e['center']['lat'] ?? null);
            $plon = $e['lon'] ?? ($e['center']['lon'] ?? null);
            $name = trim((string) ($t['name:en'] ?? $t['name'] ?? ''));
            if ($plat === null || $plon === null || $name === '') {
                continue;
            }
            $kind = match (true) {
                in_array($t['amenity'] ?? '', ['hospital', 'clinic'], true) => 'hospital',
                ($t['amenity'] ?? '') === 'police' => 'police',
                ($t['amenity'] ?? '') === 'pharmacy' => 'pharmacy',
                default => 'grocery',
            };
            $groups[$kind][] = $this->essentialRow($name, $lat, $lon, (float) $plat, (float) $plon) + ['clinic' => ($t['amenity'] ?? '') === 'clinic'];
        }

        // Hospitals before clinics, then nearest first; two of each is plenty on a phone.
        return collect($groups)->map(fn ($rows) => collect($rows)->sortBy([['clinic', 'asc'], ['km', 'asc']])->unique('name')
            ->take(2)->map(fn ($r) => \Illuminate\Support\Arr::except($r, ['clinic']))->values()->all())->all();
    }

    /**
     * Where is this named place, near this point? One Photon lookup, cached 30 days (a miss for 1 day). Results more
     * than 40 km from the point are namesakes elsewhere, so they count as not found.
     *
     * @return array{lat: float, lon: float}|null
     */
    public function locate(string $name, float $lat, float $lon): ?array
    {
        $key = 'places:osm:locate:' . md5(mb_strtolower($name) . '|' . round($lat, 1) . '|' . round($lon, 1));
        $hit = Cache::get($key);
        if ($hit !== null) {
            return $hit ?: null;
        }

        $res = rescue(fn () => Http::withHeaders(['User-Agent' => self::USER_AGENT])->connectTimeout(4)->timeout(6)
            ->get(self::PHOTON_URL, ['q' => $name, 'lat' => $lat, 'lon' => $lon, 'limit' => 5, 'lang' => 'en']), null, false);
        if (! $res?->successful()) {
            return null; // not cached: try again later
        }

        $best = collect($res->json('features', []))
            ->map(fn ($f) => [
                'lat' => (float) data_get($f, 'geometry.coordinates.1'), 'lon' => (float) data_get($f, 'geometry.coordinates.0'),
            ])
            ->filter(fn ($p) => $p['lat'] && $p['lon'] && self::km($lat, $lon, $p['lat'], $p['lon']) <= 40)
            ->sortBy(fn ($p) => self::km($lat, $lon, $p['lat'], $p['lon']))->first();

        Cache::put($key, $best ? ['lat' => round($best['lat'], 5), 'lon' => round($best['lon'], 5)] : false, $best ? now()->addDays(30) : now()->addDay());

        return $best ? ['lat' => round($best['lat'], 5), 'lon' => round($best['lon'], 5)] : null;
    }

    /** Full answer from Geoapify (4 small calls) when a GEOAPIFY_KEY is configured; null otherwise or on any failure. */
    private function essentialsFromGeoapify(float $lat, float $lon): ?array
    {
        $apiKey = config('services.geoapify.key');
        if (! filled($apiKey)) {
            return null;
        }

        $groups = [];
        foreach (['hospital' => 'healthcare.hospital', 'police' => 'service.police', 'pharmacy' => 'healthcare.pharmacy', 'grocery' => 'commercial.supermarket'] as $kind => $category) {
            $res = rescue(fn () => Http::connectTimeout(4)->timeout(7)->get('https://api.geoapify.com/v2/places', [
                'categories' => $category, 'filter' => "circle:{$lon},{$lat},6000", 'bias' => "proximity:{$lon},{$lat}", 'limit' => 6, 'lang' => 'en', 'apiKey' => $apiKey,
            ]), null, false);
            if (! $res?->successful() || ! is_array($res->json('features'))) {
                return null;
            }
            $groups[$kind] = collect($res->json('features'))->map(function ($f) use ($lat, $lon) {
                $name = trim((string) data_get($f, 'properties.name', ''));
                $plon = data_get($f, 'geometry.coordinates.0', data_get($f, 'properties.lon'));
                $plat = data_get($f, 'geometry.coordinates.1', data_get($f, 'properties.lat'));

                return ($name === '' || $plat === null || $plon === null) ? null : $this->essentialRow($name, $lat, $lon, (float) $plat, (float) $plon);
            })->filter()->sortBy('km')->unique('name')->take(2)->values()->all();
        }

        return $groups;
    }

    /** Photon (komoot) finds well-named hospitals and police stations fast, but not pharmacies or shops. @return array<int, array<string, mixed>> */
    private function essentialsFromPhoton(float $lat, float $lon, string $q, string $osmTag): array
    {
        $res = rescue(fn () => Http::withHeaders(['User-Agent' => self::USER_AGENT])->connectTimeout(4)->timeout(6)
            ->get(self::PHOTON_URL, ['q' => $q, 'lat' => $lat, 'lon' => $lon, 'osm_tag' => $osmTag, 'limit' => 8, 'lang' => 'en']), null, false);
        if (! $res?->successful()) {
            return [];
        }

        return collect($res->json('features', []))
            ->map(function ($f) use ($lat, $lon) {
                $name = trim((string) data_get($f, 'properties.name', ''));
                $plon = data_get($f, 'geometry.coordinates.0');
                $plat = data_get($f, 'geometry.coordinates.1');

                return ($name === '' || $plat === null || $plon === null) ? null : $this->essentialRow($name, $lat, $lon, (float) $plat, (float) $plon);
            })
            ->filter(fn ($r) => $r && $r['km'] <= 15)   // Photon only biases by location, so drop far-away namesakes
            ->sortBy('km')->unique('name')->take(2)->values()->all();
    }

    /** @return array{name:string, km:float, lat:float, lon:float, map_url:string} */
    private function essentialRow(string $name, float $lat, float $lon, float $plat, float $plon): array
    {
        return [
            'name' => $name,
            'km' => round(self::km($lat, $lon, $plat, $plon), 1),
            'lat' => round($plat, 5),
            'lon' => round($plon, 5),
            'map_url' => 'https://www.google.com/maps/search/?api=1&query=' . urlencode("{$plat},{$plon}"),
        ];
    }

    /** Great-circle distance in km. */
    public static function km(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $r = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;

        return 2 * $r * asin(min(1, sqrt($a)));
    }

    /** A Booking-style label for an OSM type: "Neighbourhood", "Hotel", "Airport"… */
    public static function categoryLabel(?string $key, ?string $value): string
    {
        return match (true) {
            $key === 'tourism' && $value === 'hotel' => 'Hotel',
            $key === 'tourism' && $value === 'hostel' => 'Hostel',
            $key === 'tourism' && $value === 'guest_house' => 'Guesthouse',
            $key === 'tourism' && $value === 'motel' => 'Motel',
            $key === 'tourism' && $value === 'apartment' => 'Apartment',
            $key === 'tourism' && in_array($value, ['museum', 'gallery'], true) => 'Museum',
            $key === 'tourism' && $value === 'viewpoint' => 'Viewpoint',
            $key === 'tourism' => 'Attraction',
            $key === 'aeroway' => 'Airport',
            $key === 'historic' => 'Historic site',
            $key === 'amenity' && $value === 'place_of_worship' => 'Temple / church',
            $key === 'leisure' => 'Park',
            $key === 'place' && in_array($value, ['city', 'town'], true) => 'City',
            $key === 'place' && in_array($value, ['village', 'hamlet'], true) => 'Town',
            $key === 'place' && in_array($value, ['suburb', 'quarter', 'neighbourhood', 'borough'], true) => 'Neighbourhood',
            $key === 'boundary' || $key === 'place' => 'Area',
            default => 'Place',
        };
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
            'category' => self::categoryLabel($p['osm_key'] ?? null, $p['osm_value'] ?? null),
            'district' => ($p['district'] ?? null) ?: ($p['locality'] ?? null),
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
