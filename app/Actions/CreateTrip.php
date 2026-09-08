<?php

namespace App\Actions;

use App\Models\Trip;
use App\Models\User;
use App\Support\OsmMap;
use Carbon\CarbonPeriod;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Build a fresh trip skeleton from the "new trip" wizard: flights + dates +
 * hotel + areas + interests + shopping. Produces one day per date with the
 * arrival / hotel / departure stops filled and each middle day anchored to an
 * area — enough structure for the AI day-fill (or the user) to flesh out.
 */
class CreateTrip
{
    /**
     * @param  array<string, mixed>  $data  validated payload from StoreTripRequest
     */
    public function __invoke(array $data, User $owner): Trip
    {
        $data += [
            'title' => '', 'party_size' => 2, 'currency' => 'USD',
            'segments' => [], 'areas' => [], 'interests' => [], 'shopping' => [],
            'hotel_name' => null, 'hotel_address' => null, 'hotel_lat' => null, 'hotel_lon' => null,
            'budget_per_person' => null,
        ];

        return DB::transaction(function () use ($data, $owner) {
            $arrival = Carbon::parse($data['arrival_date']);
            $departure = Carbon::parse($data['departure_date']);
            $areas = collect($data['areas'])->filter(fn ($a) => filled($a['name'] ?? null))->values();

            $trip = Trip::create([
                'slug' => $this->uniqueSlug($data['title'] ?: $data['destination']),
                'title' => $data['title'] ?: "{$owner->name}'s {$data['destination']} trip",
                'destination' => $data['destination'],
                'origin_label' => trim(($data['segments'][0]['from'] ?? '') . ' <-> ' . ($data['segments'][0]['to'] ?? ''), ' <->') ?: null,
                'start_date' => $arrival,
                'end_date' => $departure,
                'party_size' => $data['party_size'] ?? 2,
                'currency' => $data['currency'] ?? 'USD',
                'map_provider' => 'google',
                'lat' => $data['hotel_lat'] ?? ($areas[0]['lat'] ?? null),
                'lon' => $data['hotel_lon'] ?? ($areas[0]['lon'] ?? null),
                'hotel_name' => $data['hotel_name'] ?? null,
                'hotel_address' => $data['hotel_address'] ?? null,
                'interests' => [
                    'interests' => $data['interests'] ?? [],
                    'shopping' => array_keys($data['shopping'] ?? []),
                    'budget_per_person' => $data['budget_per_person'] ?? null,
                ],
                'forecast_note' => 'Each day shows the live forecast for that day\'s actual area, not just the hotel. Numbers fill in once the dates fall inside the ~16-day window.',
                'segments' => $this->segments($data['segments'] ?? []),
                'stats' => [
                    ['value' => (string) ($arrival->diffInDays($departure)), 'label' => 'nights'],
                    ['value' => (string) max($areas->count(), 1), 'label' => 'areas'],
                    ['value' => (string) ($data['party_size'] ?? 2), 'label' => 'travellers'],
                ],
                'is_public' => false,
                'created_by' => $owner->id,
            ]);

            $trip->members()->attach($owner->id, ['role' => 'owner']);

            $dates = collect(CarbonPeriod::create($arrival, $departure))->values();
            $lastIndex = $dates->count() - 1;

            foreach ($dates as $i => $date) {
                $this->buildDay($trip, $i, $lastIndex, $date, $data, $areas);
            }

            foreach ($this->budgetLines($data, $areas->count()) as $j => $line) {
                $trip->budgetLines()->create($line + ['sort' => $j]);
            }

            return $trip;
        });
    }

    private function buildDay(Trip $trip, int $i, int $lastIndex, Carbon $date, array $data, $areas): void
    {
        $out = $data['segments'][0] ?? [];
        $ret = $data['segments'][1] ?? [];
        $hotel = $data['hotel_name'] ?? 'your hotel';
        $mapFor = fn (?float $lat, ?float $lon) => ($lat && $lon) ? OsmMap::embedUrl($lat, $lon) : null;

        $common = [
            'day_number' => $i + 1,
            'date' => $date,
            'forecast_date' => $date,
            'weather_tag' => 'outdoor',
            'weather_note' => 'Forecast for this day fills in closer to the trip. Pack for the season and keep a wet-weather option.',
            'source' => 'skeleton',
            'sort' => $i,
            'lat' => $trip->lat,
            'lon' => $trip->lon,
            'map_embed_url' => $mapFor($trip->lat, $trip->lon),
            'hiccups' => [
                'Check opening hours and weekly closing days the morning of — plenty of sites shut one weekday.',
                'Have an indoor fallback for this area in case the forecast turns.',
            ],
        ];

        if ($i === 0) {
            $day = $trip->days()->create($common + [
                'title' => 'Arrival & settle in',
                'area_label' => $data['hotel_name'] ? "Base: {$data['hotel_name']}" : 'Settle in near the hotel',
                'summary' => 'Land, drop bags, and stay close — a short first outing near the hotel, not a full day.',
            ]);

            $day->stops()->create(['sort' => 0, 'time' => $out['arrive'] ?? null, 'has_options' => false,
                'title' => 'Land at ' . ($out['to'] ?: 'the airport'),
                'description' => trim(($out['airline'] ?? '') . ' ' . ($out['flight_no'] ?? '')) . '. Immigration, bags, then a transit card and the ride into town.']);
            $day->stops()->create(['sort' => 1, 'time' => null, 'has_options' => false,
                'title' => "Bag drop at {$hotel}",
                'description' => 'Front desk holds luggage until check-in. This is your base for the trip.',
                'map_url' => filled($data['hotel_address'] ?? null) ? 'https://www.google.com/maps/search/?api=1&query=' . urlencode($data['hotel_address']) : null]);
            $day->stops()->create(['sort' => 2, 'time' => '16:00', 'has_options' => false,
                'title' => 'Short ease-in outing near the hotel',
                'description' => 'One low-effort thing within ~30 minutes — a park, a market, a viewpoint — then an early dinner.']);

            return;
        }

        if ($i === $lastIndex) {
            $day = $trip->days()->create($common + [
                'title' => 'Departure day',
                'area_label' => 'Last morning, then the airport',
                'summary' => 'Bags travel with you. One easy thing if there\'s time, then head out.',
            ]);

            $day->stops()->create(['sort' => 0, 'time' => '10:00', 'has_options' => false,
                'title' => "Checkout — bags with you or held at {$hotel}",
                'description' => 'Front desk holds luggage until the evening if the flight is late.']);
            $day->stops()->create(['sort' => 1, 'time' => null, 'has_options' => false,
                'title' => 'One last easy thing, if time', 'description' => 'A neighbourhood walk, a last meal, a final shop.']);
            $day->stops()->create(['sort' => 2, 'time' => null, 'has_options' => false,
                'title' => 'Head to ' . ($ret['from'] ?: 'the airport'),
                'description' => 'Leave a buffer — airport transfers back are slower than you expect.']);
            $day->stops()->create(['sort' => 3, 'time' => $ret['depart'] ?? null, 'has_options' => false,
                'title' => 'Depart — ' . trim(($ret['airline'] ?? '') . ' ' . ($ret['flight_no'] ?? '')) ?: 'flight home',
                'description' => 'Trip complete.']);

            return;
        }

        // Middle day → anchor to an area (round-robin).
        $area = $areas->isNotEmpty() ? $areas[($i - 1) % $areas->count()] : null;
        $name = $area['name'] ?? 'Free day — revisit a favourite';

        $areaLat = $area['lat'] ?? $trip->lat;
        $areaLon = $area['lon'] ?? $trip->lon;

        $day = $trip->days()->create(array_merge($common, [
            'title' => $name,
            'area_label' => $name,
            'lat' => $areaLat,
            'lon' => $areaLon,
            'map_embed_url' => $mapFor($areaLat, $areaLon),
            'summary' => "A day around {$name}. Fill the slots below, or let the draft-with-AI button suggest options.",
        ]));

        foreach ([
            ['09:00', "Morning in {$name}", 'The main sight or walk for this area, done before the crowds and the heat.'],
            ['12:30', "Lunch near {$name}", 'Something local — leave it open, or pin a spot with place search.'],
            ['14:30', "Afternoon in {$name}", 'A second sight, a market, or a slower wander.'],
            ['19:00', 'Dinner', 'Back near the hotel, or wherever the day ended up.'],
        ] as $k => [$time, $title, $desc]) {
            $day->stops()->create(['sort' => $k, 'time' => $time, 'title' => $title, 'description' => $desc, 'has_options' => false]);
        }
    }

    /** @return array<int, array<string, mixed>> */
    private function segments(array $rows): array
    {
        return collect($rows)
            ->filter(fn ($r) => filled($r['from'] ?? null) || filled($r['to'] ?? null))
            ->map(fn ($r) => [
                'from' => $r['from'] ?? '', 'to' => $r['to'] ?? '',
                'date' => $r['date'] ?? '', 'depart' => $r['depart'] ?? '', 'arrive' => $r['arrive'] ?? '',
                'terminal' => $r['terminal'] ?? '', 'airline' => $r['airline'] ?? '', 'flight_no' => $r['flight_no'] ?? '',
            ])->values()->all();
    }

    /** @return array<int, array<string, mixed>> */
    private function budgetLines(array $data, int $areaCount): array
    {
        $lines = [
            ['category' => 'Transpo', 'label' => 'Round-trip airfare', 'amount' => 0],
            ['category' => 'Transpo', 'label' => 'Airport transfers', 'amount' => 0],
            ['category' => 'Transpo', 'label' => 'Local transport', 'note' => 'passes, day tickets', 'amount' => 0],
            ['category' => 'Accommodation', 'label' => 'Hotel', 'note' => $data['hotel_name'] ?? null, 'amount' => 0],
            ['category' => 'Meals', 'label' => 'Per diem', 'note' => 'per meal x days', 'amount' => 0, 'per_person' => true],
            ['category' => 'Rail & entry', 'label' => 'Attraction & activity tickets', 'note' => $areaCount . ' areas', 'amount' => 0],
            ['category' => 'Insurance', 'label' => 'Travel insurance', 'amount' => 0],
            ['category' => 'Communication', 'label' => 'eSIM / pocket wifi', 'amount' => 0],
        ];

        $shopLabels = [
            'electronics' => 'Electronics', 'stationery' => 'Stationery',
            'clothes' => 'Clothing', 'beauty' => 'Cosmetics & beauty',
            'food' => 'Food & edible souvenirs', 'homeware' => 'Homeware',
            'souvenirs' => 'Souvenirs & gifts',
        ];
        foreach (($data['shopping'] ?? []) as $key => $amount) {
            $lines[] = [
                'category' => 'Shopping',
                'label' => $shopLabels[$key] ?? Str::title($key),
                'amount' => is_numeric($amount) ? (int) $amount : 0,
            ];
        }

        return $lines;
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'trip';
        $slug = $base;
        $i = 2;
        while (Trip::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
