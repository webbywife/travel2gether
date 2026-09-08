<?php

namespace App\Actions;

use App\Models\Place;
use App\Models\StopOption;
use Illuminate\Support\Str;

/**
 * Registers every named option the AI generates into the shared `places`
 * index — deduped by name, so "Kiyomizu-dera Temple" collapses to one row
 * no matter how many trips/days suggest it. No Places API key needed to
 * index; once one is configured, `PlacesService::details()` can backfill
 * lat/lon/rating for these same rows by matching on name.
 */
class IndexGeneratedPlaces
{
    private const PROVIDER = 'gemini';

    /** @param  iterable<StopOption>  $options */
    public function __invoke(iterable $options): int
    {
        $indexed = 0;

        foreach ($options as $option) {
            $name = trim((string) $option->name);
            $slug = Str::slug($name);

            if ($name === '' || $slug === '') {
                continue;
            }

            $place = Place::firstOrCreate(
                ['provider' => self::PROVIDER, 'provider_id' => $slug],
                ['name' => $name, 'types' => $option->tier ? [$option->tier] : null],
            );

            // Link back so thumbs-up/down on this option roll up to the shared
            // place record — the same restaurant suggested on two trips shares one score.
            if ($option->place_id !== $place->id) {
                $option->forceFill(['place_id' => $place->id])->save();
            }

            $indexed++;
        }

        return $indexed;
    }
}
