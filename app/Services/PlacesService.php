<?php

namespace App\Services;

use App\Models\Place;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * POI search + indexing via the Google Places API (New).
 *
 * Search responses are cached (cheap repeats, feeds Phase 6 analytics);
 * place details are persisted into the `places` table and refreshed
 * every 30 days.
 */
class PlacesService
{
    private const SEARCH_URL = 'https://places.googleapis.com/v1/places:searchText';
    private const DETAILS_URL = 'https://places.googleapis.com/v1/places/';

    private ?string $key;

    public function __construct(?string $key = null)
    {
        $this->key = $key ?? config('services.google.maps_key');
    }

    public function enabled(): bool
    {
        return filled($this->key);
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
