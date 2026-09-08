<?php

namespace App\Actions;

use App\Models\Trip;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DuplicateTrip
{
    /**
     * Deep-copy a trip (days → stops → options, budget lines) into a new
     * private trip owned by $owner.
     */
    public function __invoke(Trip $source, User $owner, ?string $title = null): Trip
    {
        return DB::transaction(function () use ($source, $owner, $title) {
            $source->loadMissing('days.stops.options', 'budgetLines');

            $copy = Trip::create([
                'slug' => $this->uniqueSlug($title ?? $source->title),
                'title' => $title ?? ($source->title . ' (copy)'),
                'tagline' => $source->tagline,
                'subhead' => $source->subhead,
                'destination' => $source->destination,
                'origin_label' => $source->origin_label,
                'start_date' => $source->start_date,
                'end_date' => $source->end_date,
                'party_size' => $source->party_size,
                'currency' => $source->currency,
                'map_provider' => $source->map_provider,
                'lat' => $source->lat,
                'lon' => $source->lon,
                'forecast_note' => $source->forecast_note,
                'segments' => $source->segments,
                'stats' => $source->stats,
                'is_public' => false,
                'created_by' => $owner->id,
            ]);

            $copy->members()->attach($owner->id, ['role' => 'owner']);

            foreach ($source->days as $day) {
                $newDay = $copy->days()->create($day->only([
                    'day_number', 'date', 'title', 'title_secondary', 'summary',
                    'weather_tag', 'forecast_date', 'temp_high', 'temp_low', 'weather_note',
                    'outfit_chips', 'outfit_photos', 'area_label', 'map_embed_url', 'hiccups', 'sort',
                ]));

                foreach ($day->stops as $stop) {
                    $newStop = $newDay->stops()->create($stop->only([
                        'sort', 'time', 'title', 'description', 'cost_label', 'weather_tag',
                        'map_provider', 'map_url', 'thumb_url', 'hiccup', 'has_options', 'option_label',
                    ]));

                    foreach ($stop->options as $option) {
                        $newStop->options()->create($option->only([
                            'sort', 'name', 'tier', 'note', 'cost_min', 'cost_max', 'currency',
                            'weather_tag', 'map_provider', 'map_url', 'is_default_pick', 'is_sponsored',
                        ]));
                    }
                }
            }

            foreach ($source->budgetLines as $line) {
                $copy->budgetLines()->create($line->only([
                    'sort', 'category', 'label', 'note', 'amount', 'per_person',
                ]));
            }

            return $copy;
        });
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
