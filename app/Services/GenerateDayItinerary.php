<?php

namespace App\Services;

use App\Models\TripDay;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

/**
 * Drafts one day of an itinerary with Gemini: stops with 3+ options each,
 * a weather note contextualised to the day's actual area + date, outfit
 * chips, a rationale, and documented hiccups. Grounded with real POIs from
 * Places search when a maps key is configured.
 */
class GenerateDayItinerary
{
    private ?string $key;
    private string $model;

    public function __construct(private PlacesService $places)
    {
        $this->key = config('services.gemini.api_key');
        $this->model = config('services.gemini.model', 'gemini-3.6-flash');
    }

    public function enabled(): bool
    {
        return filled($this->key);
    }

    /**
     * @return array<string, mixed>  keys: weather_note, weather_tag, outfit_chips,
     *                               summary, hiccups, stops
     *
     * @throws \RuntimeException on an unusable response
     */
    public function forDay(TripDay $day): array
    {
        abort_unless($this->enabled(), 503, 'AI drafting is not configured.');

        $trip = $day->trip;
        $area = $day->area_label ?: $day->title ?: $trip->destination;
        $prefs = $trip->interests ?? [];

        $grounding = $this->groundingPois(
            $area,
            (float) ($day->lat ?? $trip->lat),
            (float) ($day->lon ?? $trip->lon),
            (array) ($prefs['interests'] ?? []),
        );

        $prompt = $this->prompt($trip, $day, $area, $prefs, $grounding);

        // The model occasionally returns a truncated / degenerate blob, or a 429 under
        // load — retry a few times with a short backoff.
        $data = null;
        for ($attempt = 0; $attempt < 3 && $data === null; $attempt++) {
            if ($attempt > 0) {
                usleep(1_500_000 * $attempt);
            }
            $data = $this->callOnce($prompt);
        }

        if (! is_array($data) || empty($data['stops'])) {
            throw new \RuntimeException('Gemini returned an unusable draft.');
        }

        return $this->normalize($data);
    }

    /** @return array<string, mixed>|null */
    private function callOnce(string $prompt): ?array
    {
        $response = Http::timeout(45)->post(
            "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->key}",
            [
                'contents' => [['role' => 'user', 'parts' => [['text' => $prompt]]]],
                'generationConfig' => [
                    'responseMimeType' => 'application/json',
                    'temperature' => 0.35,
                    'maxOutputTokens' => 16384,
                    'thinkingConfig' => ['thinkingBudget' => 1024],
                ],
            ],
        );

        if (! $response->successful()) {
            if ($response->status() >= 500 || $response->status() === 429) {
                return null; // retryable
            }
            throw new \RuntimeException('Gemini request failed: ' . $response->status());
        }

        $text = (string) data_get($response->json(), 'candidates.0.content.parts.0.text');
        $data = json_decode($text, true);

        return (is_array($data) && ! empty($data['stops'])) ? $data : null;
    }

    /** @return array<int, array{name:string, address:?string, lat:?float, lon:?float}> */
    private function groundingPois(string $area, ?float $lat, ?float $lon, array $interests): array
    {
        if (! $this->places->enabled() || ! $lat || ! $lon) {
            return [];
        }

        $queries = array_slice(array_merge(
            ["things to do in {$area}", "restaurants in {$area}"],
            array_map(fn ($i) => "{$i} in {$area}", array_slice($interests, 0, 2)),
        ), 0, 4);

        return collect($queries)
            ->flatMap(fn ($q) => $this->places->search($q, $lat, $lon, 4))
            ->unique('provider_id')
            ->take(16)
            ->map(fn ($p) => [
                'name' => $p['name'],
                'address' => $p['formatted_address'] ?? null,
                'lat' => $p['lat'] ?? null,
                'lon' => $p['lon'] ?? null,
            ])
            ->values()
            ->all();
    }

    private function prompt($trip, TripDay $day, string $area, array $prefs, array $grounding): string
    {
        $interests = implode(', ', (array) ($prefs['interests'] ?? [])) ?: 'general sightseeing';
        $shopping = implode(', ', (array) ($prefs['shopping'] ?? []));
        $poiList = $grounding
            ? "\nReal places nearby (prefer these; use the exact names):\n- " . implode("\n- ", array_map(
                fn ($p) => $p['name'] . ($p['address'] ? " ({$p['address']})" : ''), $grounding))
            : '';

        $cur = $trip->currency;

        return <<<PROMPT
        Plan ONE day of a group trip to {$trip->destination}. Output a single JSON object
        exactly in the shape shown below. No markdown, no comments, no text outside the JSON.

        This day's area: {$area}
        Date: {$day->date->toFormattedDateString()} ({$day->date->format('l')})
        Group size: {$trip->party_size}   Costs in: {$cur}
        Interests: {$interests}
        {$this->shoppingLine($shopping)}
        Base hotel: {$trip->hotel_name}
        {$poiList}

        Rules:
        - weather_note: 1-2 sentences, specific to {$area}'s geography (coast/mountain/city/
          valley) and this date's season. Not generic.
        - weather_tag: one of "indoor", "covered", "outdoor".
        - 5-7 stops, time-ordered, ~09:00 to evening: a morning sight, lunch, an afternoon
          thing, dinner, and one shopping stop if it fits the interests.
        - EVERY meal / sight / activity / shopping stop needs 3-4 real named options at
          different tiers ("budget", "mid", "splurge", and an "indoor-ac" or "rain-friendly"
          where useful). costs are integers in {$cur}, 0 for free.
        - A pure transit hop or a checkout has "options": [].
        - Prefer the real places listed above; use their exact names.

        Shape (fill with real content for {$area}):
        {
          "weather_note": "…",
          "weather_tag": "outdoor",
          "outfit_chips": ["…", "…", "…"],
          "summary": "…",
          "hiccups": ["…", "…", "…"],
          "stops": [
            {
              "time": "09:00",
              "title": "Morning at …",
              "description": "one line of context",
              "option_label": "Which temple",
              "options": [
                {"name": "Real Place A", "tier": "budget", "note": "one line", "cost_min": 0, "cost_max": 5, "map_query": "Real Place A {$area}"},
                {"name": "Real Place B", "tier": "mid", "note": "one line", "cost_min": 10, "cost_max": 15, "map_query": "Real Place B {$area}"},
                {"name": "Real Place C", "tier": "splurge", "note": "one line", "cost_min": 40, "cost_max": 60, "map_query": "Real Place C {$area}"}
              ]
            },
            {"time": "12:00", "title": "Transit to …", "description": "…", "options": []}
          ]
        }
        PROMPT;
    }

    private function shoppingLine(string $shopping): string
    {
        return $shopping ? "Wants to shop for: {$shopping} — work a relevant shopping stop into the day." : '';
    }


    /** @return array<string, mixed> */
    private function normalize(array $d): array
    {
        $tag = strtolower((string) ($d['weather_tag'] ?? 'outdoor'));
        $tag = in_array($tag, ['indoor', 'covered', 'outdoor'], true) ? $tag : 'outdoor';

        return [
            'weather_note' => (string) ($d['weather_note'] ?? ''),
            'weather_tag' => $tag,
            'outfit_chips' => array_values(array_filter(array_map('strval', (array) ($d['outfit_chips'] ?? [])))),
            'summary' => (string) ($d['summary'] ?? ''),
            'hiccups' => array_values(array_filter(array_map('strval', (array) ($d['hiccups'] ?? [])))) ?: ['Check opening hours the morning of.'],
            'stops' => collect($d['stops'] ?? [])->map(function ($s) {
                $options = collect($s['options'] ?? [])->map(fn ($o) => [
                    'name' => (string) ($o['name'] ?? ''),
                    'tier' => Arr::get($o, 'tier') ? (string) $o['tier'] : null,
                    'note' => (string) ($o['note'] ?? ''),
                    'cost_min' => isset($o['cost_min']) ? max(0, (int) $o['cost_min']) : null,
                    'cost_max' => isset($o['cost_max']) ? max(0, (int) $o['cost_max']) : null,
                    'map_url' => Arr::get($o, 'map_query')
                        ? 'https://www.google.com/maps/search/?api=1&query=' . urlencode($o['map_query'])
                        : null,
                ])->filter(fn ($o) => $o['name'] !== '')->values()->all();

                return [
                    'time' => Arr::get($s, 'time') ? (string) $s['time'] : null,
                    'title' => (string) ($s['title'] ?? 'Stop'),
                    'description' => (string) ($s['description'] ?? ''),
                    'cost_label' => Arr::get($s, 'cost_label') ? (string) $s['cost_label'] : null,
                    'option_label' => Arr::get($s, 'option_label') ? (string) $s['option_label'] : (count($options) ? 'Options' : null),
                    'options' => $options,
                ];
            })->filter(fn ($s) => $s['title'] !== '')->values()->all(),
        ];
    }
}
