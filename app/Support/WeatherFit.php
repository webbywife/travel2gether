<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Phase 3 — how exposed an option is to the weather: "indoor", "covered" or
 * "outdoor". New AI drafts carry an explicit tag; older/hand-entered options
 * fall back to a keyword guess from their name, tier and note.
 */
class WeatherFit
{
    public const TAGS = ['indoor', 'covered', 'outdoor'];

    private const INDOOR = [
        'museum', 'gallery', 'aquarium', 'mall', 'department store', 'shopping center', 'shopping centre',
        'cafe', 'café', 'coffee', 'restaurant', 'bistro', 'izakaya', 'ramen', 'sushi', 'diner', 'bar',
        'spa', 'onsen', 'sauna', 'jjimjilbang', 'theater', 'theatre', 'cinema', 'karaoke', 'arcade',
        'library', 'planetarium', 'observatory deck', 'indoor', 'hotel', 'bakery', 'food hall',
        'exhibition', 'studio', 'workshop', 'class', 'bookstore', 'book store', 'cooking',
    ];

    private const COVERED = [
        'covered', 'arcade street', 'shotengai', 'underground', 'station', 'food court', 'hawker',
        'indoor market', 'night market', 'market', 'temple hall', 'cathedral', 'church', 'palace hall',
        'tower', 'skydeck', 'observatory', 'castle',
    ];

    private const OUTDOOR = [
        'park', 'garden', 'beach', 'hike', 'trail', 'mountain', 'island', 'river', 'lake', 'falls',
        'waterfall', 'viewpoint', 'lookout', 'walk', 'stroll', 'street', 'alley', 'square', 'plaza',
        'zoo', 'shrine', 'temple', 'palace', 'ruins', 'cruise', 'boat', 'picnic', 'bike', 'cycling',
        'outdoor', 'rooftop', 'promenade', 'pier', 'harbour', 'harbor', 'canyon', 'national park',
    ];

    public static function normalize(?string $tag): ?string
    {
        $tag = Str::lower(trim((string) $tag));

        return in_array($tag, self::TAGS, true) ? $tag : null;
    }

    /** Whole-word / phrase match, so "walks" ≠ "walk" and "barbecue" ≠ "bar". */
    private static function has(string $text, array $words): bool
    {
        foreach ($words as $w) {
            if (preg_match('/(?<![\\pL])' . preg_quote(trim($w), '/') . '(?![\\pL])/u', $text)) {
                return true;
            }
        }

        return false;
    }

    private const MEAL_CONTEXT = [
        'breakfast', 'brunch', 'lunch', 'dinner', 'supper', 'meal', 'eat', 'food', 'bbq', 'barbecue',
        'restaurant', 'snack', 'dessert', 'drinks', 'coffee',
    ];

    /** $context = the stop's title / option label ("Dinner in Gangnam") — meals default to indoor. */
    public static function infer(?string $name, ?string $tier = null, ?string $note = null, ?string $context = null): string
    {
        $tier = Str::lower((string) $tier);
        if (Str::contains($tier, ['indoor', 'rain'])) {
            return 'indoor';
        }

        $all = Str::lower(trim("{$name} {$note}"));
        $title = Str::lower((string) $name);

        // Sheltered cues anywhere; exposure cues only in the place's own name
        // (a note like "the chef walks you through…" isn't a walk outside).
        if (self::has($all, self::INDOOR)) {
            return 'indoor';
        }
        if (self::has($all, self::COVERED)) {
            return 'covered';
        }
        if (self::has($title, self::OUTDOOR)) {
            return 'outdoor';
        }
        if (self::has(Str::lower((string) $context), self::MEAL_CONTEXT)) {
            return 'indoor';
        }

        return 'outdoor';
    }
}
