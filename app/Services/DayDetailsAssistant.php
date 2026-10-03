<?php

namespace App\Services;

use App\Models\TripDay;

/**
 * "✨ Help me with this day" on the Edit day page: suggests a distinct title,
 * area, summary, weather note and (if useful) a hotel hint for one day, using
 * the whole trip as context so days don't all end up "Golden Gate Bridge".
 * Suggestions only — nothing is saved until the user accepts and saves.
 */
class DayDetailsAssistant
{
    private const SCHEMA = [
        'type' => 'object',
        'additionalProperties' => false,
        'required' => ['title', 'title_secondary', 'area_label', 'summary', 'weather_note', 'hotel_hint', 'duplicate_note'],
        'properties' => [
            'title' => ['type' => 'string', 'description' => 'Short day title, max 60 characters'],
            'title_secondary' => ['type' => 'string', 'description' => 'Optional local-language name or subtitle; empty string if none'],
            'area_label' => ['type' => 'string', 'description' => 'Neighbourhood/area this day is based in'],
            'summary' => ['type' => 'string', 'description' => '"Why it\'s worth it", 1-2 sentences'],
            'weather_note' => ['type' => 'string', 'description' => '1 sentence on typical weather for this place and season, with what to pack'],
            'hotel_hint' => ['type' => 'string', 'description' => 'Only if this day is far from the main hotel: a suggestion for where to stay; else empty string'],
            'duplicate_note' => ['type' => 'string', 'description' => 'If several days share the same title/theme, one friendly sentence suggesting how to vary them; else empty string'],
        ],
    ];

    public function __construct(private ClaudeClient $claude) {}

    public function enabled(): bool
    {
        return $this->claude->enabled();
    }

    /**
     * @param  array<int, string>  $avoid  titles already suggested ("try different ideas")
     * @return array<string, string>
     */
    public function suggest(TripDay $day, ?string $wish = null, array $avoid = []): array
    {
        $trip = $day->trip()->with('days')->first();

        $others = $trip->days
            ->map(fn ($d) => sprintf('Day %d (%s)%s: %s%s',
                $d->day_number,
                $d->date?->format('D M j'),
                $d->id === $day->id ? ' ← THIS DAY' : '',
                $d->title,
                $d->area_label ? " — area: {$d->area_label}" : ''))
            ->implode("\n");

        $interests = implode(', ', (array) data_get($trip->interests, 'interests', []));
        $lines = [
            "Trip: {$trip->title} — {$trip->destination}",
            'Dates: ' . $trip->start_date?->toFormattedDateString() . ' to ' . $trip->end_date?->toFormattedDateString(),
            "Group size: {$trip->party_size}",
            $interests ? "Interests: {$interests}" : null,
            $trip->hotel_name ? "Main hotel: {$trip->hotel_name}" . ($trip->hotel_address ? " ({$trip->hotel_address})" : '') : null,
            '',
            'All days:',
            $others,
            '',
            "Suggest details for Day {$day->day_number} ({$day->date?->format('l, M j, Y')}).",
            "Current values — title: \"{$day->title}\", area: \"{$day->area_label}\", hotel: \"{$day->hotel_name}\".",
            $wish ? "The traveller wants this day to be about: \"{$wish}\"" : 'Keep the current idea if it is good, but make it specific and distinct from the other days.',
            $avoid ? 'Do not reuse these titles: ' . implode('; ', $avoid) : null,
        ];

        $data = $this->claude->json(
            system: 'You help travellers fill in one day of a group trip on Travel2gether. Be specific and realistic, '
                . 'use real places that fit the trip\'s destination and the season, keep titles short, and write warmly but briefly. '
                . 'Make this day clearly different from the other days unless the traveller asks otherwise.',
            user: implode("\n", array_filter($lines, fn ($l) => $l !== null)),
            schema: self::SCHEMA,
            effort: 'low',
            maxTokens: 4000,
            timeout: 60,
        );

        // Trim to the form's limits.
        $limits = ['title' => 120, 'title_secondary' => 120, 'area_label' => 120, 'summary' => 500,
            'weather_note' => 500, 'hotel_hint' => 255, 'duplicate_note' => 300];

        return collect($limits)->mapWithKeys(fn ($max, $k) => [$k => mb_substr(trim((string) ($data[$k] ?? '')), 0, $max)])->all();
    }
}
