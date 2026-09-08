<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Reads config/destinations.php — the curated most-visited-countries +
 * standout-cities + PH-passport visa reference used by the "Get inspired"
 * browse page, the trip wizard's visa hint, and the trip page's visa badge.
 */
class Destinations
{
    /** @return array<int, array<string, mixed>> */
    public static function all(): array
    {
        return config('destinations', []);
    }

    /**
     * Loosely match a free-text destination/city string (e.g. "Kyoto, Japan"
     * or "Bali") to a curated country entry.
     *
     * @return array<string, mixed>|null
     */
    public static function match(string $text): ?array
    {
        $needle = Str::lower(trim($text));
        if ($needle === '') {
            return null;
        }

        foreach (self::all() as $entry) {
            if (Str::contains($needle, Str::lower($entry['country']))) {
                return $entry;
            }
            foreach ($entry['cities'] as $city) {
                if (Str::contains($needle, Str::lower(Str::before($city, ' (')))) {
                    return $entry;
                }
            }
        }

        return null;
    }

    public static function visaLabel(string $status): string
    {
        return match ($status) {
            'visa_free' => 'Visa-free',
            'evisa' => 'e-Visa',
            'required' => 'Visa required',
            default => 'Check visa',
        };
    }
}
