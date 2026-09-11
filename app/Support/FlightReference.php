<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Resolves the free-text airport/airline fields from the trip wizard against
 * the curated lists in config/airports.php + config/airlines.php — so
 * CreateTrip can populate TripSegment's *_code columns for analytics
 * whenever it recognizes what was typed. Returns null (no match) when it
 * doesn't; the raw text is always kept regardless, so nothing is lost.
 */
class FlightReference
{
    /** @return array{code:string,name:string,city:string}|null */
    public static function airport(?string $text): ?array
    {
        return self::match($text, config('airports', []), ['name', 'city']);
    }

    /** @return array{code:string,name:string}|null */
    public static function airline(?string $text): ?array
    {
        return self::match($text, config('airlines', []), ['name']);
    }

    /**
     * @param  array<int, array<string, string>>  $list
     * @param  array<int, string>  $looseFields  fields (besides "code") to loosely match
     */
    private static function match(?string $text, array $list, array $looseFields): ?array
    {
        $needle = Str::lower(trim((string) $text));
        if ($needle === '') {
            return null;
        }

        // Exact code match first ("MNL", "PR") — the common case, since the
        // wizard's placeholders show codes and most people type them.
        foreach ($list as $entry) {
            if ($needle === Str::lower($entry['code'])) {
                return $entry;
            }
        }

        // Then a loose match on the other fields ("Philippine Airlines", "Manila").
        foreach ($list as $entry) {
            foreach ($looseFields as $field) {
                if (! isset($entry[$field])) {
                    continue;
                }
                $value = Str::lower($entry[$field]);
                if (Str::contains($needle, $value) || Str::contains($value, $needle)) {
                    return $entry;
                }
            }
        }

        return null;
    }
}
