<?php

namespace App\Support;

use App\Models\Trip;

/** Booking links for a trip, built from the URL templates in config/affiliates.php. Slots without a URL are skipped. */
class Affiliates
{
    /** @return array<int, array{key:string, label:string, url:string}> */
    public static function forTrip(Trip $trip): array
    {
        $vars = [
            '{destination}' => $trip->destination,
            '{city}' => trim(\Illuminate\Support\Str::before($trip->destination, ',')),
            '{checkin}' => $trip->start_date?->toDateString() ?? '',
            '{checkout}' => $trip->end_date?->toDateString() ?? '',
            '{adults}' => (string) max(1, (int) $trip->party_size),
        ];
        $encoded = array_map('rawurlencode', $vars);

        return collect(config('affiliates', []))
            ->filter(fn ($slot) => filled($slot['url'] ?? null) && str_starts_with((string) $slot['url'], 'https://'))
            ->map(fn ($slot, $key) => ['key' => $key, 'label' => $slot['label'], 'url' => strtr($slot['url'], $encoded)])
            ->values()->all();
    }
}
