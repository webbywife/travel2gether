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

    /**
     * The richer per-destination templates (category, season, trip length,
     * overview) — a broader, city/region-level index than all().
     *
     * @return array<int, array<string, mixed>>
     */
    public static function templates(): array
    {
        return config('destination_templates', []);
    }

    /** @return array<int, string> */
    public static function categories(): array
    {
        return collect(self::templates())->pluck('category')->unique()->sort()->values()->all();
    }

    /**
     * Loosely match free text (e.g. "Kyoto, Japan") to a destination template.
     *
     * @return array<string, mixed>|null
     */
    public static function templateFor(string $text): ?array
    {
        $needle = Str::lower(trim($text));
        if ($needle === '') {
            return null;
        }

        foreach (self::templates() as $entry) {
            if (Str::contains($needle, Str::lower($entry['destination']))) {
                return $entry;
            }
        }

        return null;
    }

    /** Visa info (from the country-level list) for a template's country. */
    public static function visaForCountry(string $country): ?array
    {
        $country = Str::lower($country);

        foreach (self::all() as $entry) {
            if (Str::lower($entry['country']) === $country) {
                return $entry;
            }
        }

        return null;
    }
}
