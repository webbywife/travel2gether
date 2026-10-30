<?php

namespace App\Actions;

use App\Models\Trip;
use App\Models\User;
use App\Support\Destinations;
use App\Support\FlightReference;
use App\Support\OsmMap;
use Carbon\CarbonPeriod;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
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
        // Every flight leg has every field (a quick plan may send none of them).
        $blankLeg = array_fill_keys(['from', 'to', 'date', 'depart', 'arrive', 'terminal', 'airline', 'flight_no'], null);
        $data['segments'] = [($data['segments'][0] ?? []) + $blankLeg, ($data['segments'][1] ?? []) + $blankLeg];

        // The step-by-step planner types a destination without picking a suggestion, so place it here.
        if (blank($data['dest_lat'] ?? null) || blank($data['dest_lon'] ?? null)) {
            $pin = app(\App\Services\PlacesService::class)->geocodeDestination($data['destination']);
            $data['dest_lat'] = $pin['lat'] ?? null;
            $data['dest_lon'] = $pin['lon'] ?? null;
        }

        return DB::transaction(function () use ($data, $owner) {
            $arrival = Carbon::parse($data['arrival_date']);
            $departure = Carbon::parse($data['departure_date']);
            $areas = collect($data['areas'])->filter(fn ($a) => filled($a['name'] ?? null))->values();
            if ($areas->isEmpty()) {
                // Quick plan: no areas given, so every day starts from the destination itself.
                $areas = collect([['name' => trim(Str::before($data['destination'], ',')),
                    'lat' => $data['dest_lat'] ?? null, 'lon' => $data['dest_lon'] ?? null]]);
            }

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
                    'pace' => $data['pace'] ?? null,
                    'who' => $data['who'] ?? null,
                    'spend' => $data['spend'] ?? null,
                    'needs' => array_values($data['needs'] ?? []),
                ],
                'forecast_note' => 'Each day shows the live forecast for that day\'s actual area, not just the hotel. Numbers fill in once the dates fall inside the ~16-day window.',
                'segments' => $this->segments($data['segments'] ?? [], $arrival, $departure),
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

            $dayAreas = $this->areasForDays($areas, max($dates->count() - 2, 0), $data);

            foreach ($dates as $i => $date) {
                $this->buildDay($trip, $i, $lastIndex, $date, $data, $areas, $dayAreas);
            }

            foreach ($this->budgetLines($data, $areas->count()) as $j => $line) {
                $trip->budgetLines()->create($line + ['sort' => $j]);
            }

            $this->storeTripSegments($trip, $data['segments'] ?? [], $arrival, $departure);

            return $trip;
        });
    }

    /**
     * Normalized copy of the flight legs, one row per segment, resolving
     * airport/airline codes against the curated reference lists when
     * possible — feeds the airline/route analytics. Purely additive; the
     * trip's own `segments` JSON (built by segments()) still drives the
     * flight-pass display and is unaffected by whether a match was found.
     */
    private function storeTripSegments(Trip $trip, array $rows, Carbon $arrival, Carbon $departure): void
    {
        $dates = [$arrival, $departure];

        foreach ($rows as $i => $r) {
            if (blank($r['from'] ?? null) && blank($r['to'] ?? null)) {
                continue;
            }

            $from = FlightReference::airport($r['from'] ?? null);
            $to = FlightReference::airport($r['to'] ?? null);
            $airline = FlightReference::airline($r['airline'] ?? null)
                ?? FlightReference::airlineFromFlightNo($r['flight_no'] ?? null);

            $trip->tripSegments()->create([
                'sort' => $i,
                'from_text' => $r['from'] ?? null,
                'to_text' => $r['to'] ?? null,
                'airline_text' => $r['airline'] ?? null,
                'from_code' => $from['code'] ?? null,
                'to_code' => $to['code'] ?? null,
                'airline_code' => $airline['code'] ?? null,
                'airline_name' => $airline['name'] ?? null,
                'date' => $dates[$i] ?? null,
                'flight_no' => $r['flight_no'] ?? null,
            ]);
        }
    }

    /**
     * One distinct area per middle day. The traveller's picks come first; when the trip has more days than picks,
     * the rest are filled with the destination's popular areas (config/destination_areas.php), then — for a
     * destination with no list, or too short a list — nearby districts and landmarks from the places lookup. Anything
     * that overlaps a pick is skipped ("Higashiyama" already covers "Higashiyama & Kiyomizu-dera"). With nothing
     * to add it leaves the rest as free days (never a repeat). Arrival and departure still use $areas, so their hotels don't change.
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function areasForDays(Collection $areas, int $middleDays, array $data): Collection
    {
        $missing = $middleDays - $areas->count();
        if ($missing <= 0) {
            return $areas;
        }

        $names = $areas->map(fn ($a) => mb_strtolower((string) $a['name']))->all();
        $overlaps = function (array $p) use (&$names): bool {
            $n = mb_strtolower((string) ($p['name'] ?? ''));
            foreach ($names as $taken) {
                if ($n === $taken || str_contains($n, $taken) || str_contains($taken, $n)) {
                    return true;
                }
            }

            return false;
        };

        $extra = collect();
        $take = function (iterable $candidates) use (&$extra, &$names, $overlaps, $missing): void {
            foreach ($candidates as $p) {
                if ($extra->count() >= $missing) {
                    return;
                }
                if (blank($p['name'] ?? null) || blank($p['lat'] ?? null) || blank($p['lon'] ?? null) || $overlaps($p)) {
                    continue;
                }
                $extra->push(['name' => $p['name'], 'lat' => $p['lat'], 'lon' => $p['lon']]);
                $names[] = mb_strtolower($p['name']);
            }
        };

        $take(Destinations::popularAreas((string) ($data['destination'] ?? '')));

        if ($extra->count() < $missing && filled($data['dest_lat'] ?? null) && filled($data['dest_lon'] ?? null)) {
            $found = rescue(fn () => app(\App\Services\PlacesService::class)->explore((float) $data['dest_lat'], (float) $data['dest_lon']), null, false);
            $take(array_merge($found['areas'] ?? [], $found['landmarks'] ?? []));
        }

        return $areas->concat($extra)->values();
    }

    private function buildDay(Trip $trip, int $i, int $lastIndex, Carbon $date, array $data, $areas, $dayAreas): void
    {
        $out = $data['segments'][0] ?? [];
        $ret = $data['segments'][1] ?? [];
        $mapFor = fn (?float $lat, ?float $lon) => ($lat && $lon) ? OsmMap::embedUrl($lat, $lon) : null;

        $tripHotel = [
            'name' => $data['hotel_name'] ?? null, 'address' => $data['hotel_address'] ?? null,
            'lat' => $data['hotel_lat'] ?? null, 'lon' => $data['hotel_lon'] ?? null,
        ];

        // Multi-city: an area can carry its own hotel (step 04). Arrival uses
        // the first area's hotel (that's the city you land in), departure the
        // last area's (the city you fly out of), each falling back to the
        // trip's single main hotel when the area didn't set one of its own.
        $hotelFor = function (?array $area) use ($tripHotel) {
            if ($area && filled($area['hotel_name'] ?? null)) {
                return [
                    'name' => $area['hotel_name'], 'address' => $area['hotel_address'] ?? null,
                    'lat' => $area['hotel_lat'] ?? null, 'lon' => $area['hotel_lon'] ?? null,
                ];
            }

            return $tripHotel;
        };

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
            $hotel = $hotelFor($areas->first());
            $hotelName = $hotel['name'] ?: 'your hotel';

            $day = $trip->days()->create($common + [
                'title' => 'Arrival & settle in',
                'area_label' => $hotel['name'] ? "Base: {$hotel['name']}" : 'Settle in near the hotel',
                'summary' => 'Land, drop bags, and stay close — a short first outing near the hotel, not a full day.',
                'hotel_name' => $hotel['name'], 'hotel_address' => $hotel['address'],
                'hotel_lat' => $hotel['lat'], 'hotel_lon' => $hotel['lon'],
            ]);

            $day->stops()->create(['sort' => 0, 'time' => $out['arrive'] ?? null, 'has_options' => false, 'is_anchor' => true,
                'title' => 'Land at ' . ($out['to'] ?: 'the airport'),
                'description' => ltrim(trim(($out['airline'] ?? '') . ' ' . ($out['flight_no'] ?? '')) . '. ', '. ') . 'Immigration, bags, then a transit card and the ride into town.']);
            $day->stops()->create(['sort' => 1, 'time' => null, 'has_options' => false, 'is_anchor' => true,
                'title' => "Bag drop at {$hotelName}",
                'description' => 'Front desk holds luggage until check-in. This is your base for the trip.',
                'map_url' => filled($hotel['address']) ? 'https://www.google.com/maps/search/?api=1&query=' . urlencode($hotel['address']) : null]);
            $day->stops()->create(['sort' => 2, 'time' => '16:00', 'has_options' => false,
                'title' => 'Short ease-in outing near the hotel',
                'description' => 'One low-effort thing within ~30 minutes — a park, a market, a viewpoint — then an early dinner.']);

            return;
        }

        if ($i === $lastIndex) {
            $hotel = $hotelFor($areas->last());
            $hotelName = $hotel['name'] ?: 'your hotel';

            $day = $trip->days()->create($common + [
                'title' => 'Departure day',
                'area_label' => 'Last morning, then the airport',
                'summary' => 'Bags travel with you. One easy thing if there\'s time, then head out.',
                'hotel_name' => $hotel['name'], 'hotel_address' => $hotel['address'],
                'hotel_lat' => $hotel['lat'], 'hotel_lon' => $hotel['lon'],
            ]);

            $day->stops()->create(['sort' => 0, 'time' => '10:00', 'has_options' => false, 'is_anchor' => true,
                'title' => "Checkout — bags with you or held at {$hotelName}",
                'description' => 'Front desk holds luggage until the evening if the flight is late.']);
            $day->stops()->create(['sort' => 1, 'time' => null, 'has_options' => false,
                'title' => 'One last easy thing, if time', 'description' => 'A neighbourhood walk, a last meal, a final shop.']);
            $day->stops()->create(['sort' => 2, 'time' => null, 'has_options' => false, 'is_anchor' => true,
                'title' => 'Head to ' . ($ret['from'] ?: 'the airport'),
                'description' => 'Leave a buffer — airport transfers back are slower than you expect.']);
            $day->stops()->create(['sort' => 3, 'time' => $ret['depart'] ?? null, 'has_options' => false, 'is_anchor' => true,
                'title' => 'Depart — ' . trim(($ret['airline'] ?? '') . ' ' . ($ret['flight_no'] ?? '')) ?: 'flight home',
                'description' => 'Trip complete.']);

            return;
        }

        // Middle day → anchor to its own area (one per day while there are enough),
        // and to that area's own hotel when it has one. Days beyond the areas are
        // free days — never a repeat of an earlier day's area.
        $area = $dayAreas[$i - 1] ?? null;
        $name = $area['name'] ?? 'Free day — revisit a favourite';
        $hotel = $hotelFor($area);

        $areaLat = $area['lat'] ?? $trip->lat;
        $areaLon = $area['lon'] ?? $trip->lon;

        $day = $trip->days()->create(array_merge($common, [
            'title' => $name,
            'area_label' => $name,
            'lat' => $areaLat,
            'lon' => $areaLon,
            'map_embed_url' => $mapFor($areaLat, $areaLon),
            'summary' => "A day around {$name}. Fill the slots below, or let the draft-with-AI button suggest options.",
            'hotel_name' => $hotel['name'], 'hotel_address' => $hotel['address'],
            'hotel_lat' => $hotel['lat'], 'hotel_lon' => $hotel['lon'],
        ]));

        foreach (self::blankSlots($name) as $stop) {
            $day->stops()->create($stop);
        }
    }

    /** The empty morning / lunch / afternoon / dinner slots a middle day starts with (also what "Clear day" goes back to). */
    public static function blankSlots(string $name): array
    {
        return collect([
            ['09:00', "Morning in {$name}", 'The main sight or walk for this area, done before the crowds and the heat.'],
            ['12:30', "Lunch near {$name}", 'Something local — leave it open, or pin a spot with place search.'],
            ['14:30', "Afternoon in {$name}", 'A second sight, a market, or a slower wander.'],
            ['19:00', 'Dinner', 'Back near the hotel, or wherever the day ended up.'],
        ])->map(fn ($s, $k) => ['sort' => $k, 'time' => $s[0], 'title' => $s[1], 'description' => $s[2], 'has_options' => false])->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     *
     * The "date shown" on the flight pass used to be a free-text field the
     * wizard asked for by hand — easy to typo or leave stale against the
     * real arrival/departure date. It's computed here instead: segment 0
     * (outbound) uses the arrival date, segment 1 (return) the departure
     * date — the only two real dates the wizard actually collects.
     */
    private function segments(array $rows, Carbon $arrival, Carbon $departure): array
    {
        $dates = [$arrival, $departure];

        return collect($rows)
            ->filter(fn ($r) => filled($r['from'] ?? null) || filled($r['to'] ?? null))
            ->map(fn ($r, $i) => [
                'from' => $r['from'] ?? '', 'to' => $r['to'] ?? '',
                'date' => isset($dates[$i]) ? strtoupper($dates[$i]->format('D d M Y')) : ($r['date'] ?? ''),
                'depart' => $r['depart'] ?? '', 'arrive' => $r['arrive'] ?? '',
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
