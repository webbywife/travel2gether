<?php

namespace Database\Seeders\Concerns;

use App\Models\Trip;
use Illuminate\Support\Facades\DB;

/**
 * Shared plumbing for the public sample trips: (re)create the trip, its days,
 * stops, options and budget lines in one transaction. Samples have no owner
 * (created_by null), which is what makes them public samples.
 */
trait BuildsSampleTrip
{
    protected function seedTrip(array $trip, array $days, array $budget): Trip
    {
        return DB::transaction(function () use ($trip, $days, $budget) {
            Trip::where('slug', $trip['slug'])->delete();
            $model = Trip::create($trip + ['is_public' => true, 'map_provider' => 'google']);

            foreach ($days as $i => $day) {
                $stops = $day['stops'];
                unset($day['stops']);
                $tripDay = $model->days()->create($day + ['sort' => $i, 'outfit_photos' => $this->outfitIdeas()]);

                foreach ($stops as $j => $stop) {
                    $options = $stop['options'] ?? [];
                    unset($stop['options']);
                    $record = $tripDay->stops()->create($stop + ['sort' => $j, 'has_options' => count($options) > 0]);
                    foreach ($options as $k => $option) {
                        $record->options()->create($option + ['sort' => $k]);
                    }
                }
            }

            foreach ($budget as $i => $line) {
                $model->budgetLines()->create($line + ['sort' => $i]);
            }

            return $model;
        });
    }

    protected function opt(string $name, ?string $tier, ?string $meta, string $note, ?int $min, ?int $max, bool $pick = false): array
    {
        return [
            'name' => $name,
            'tier' => $tier,
            'note' => trim(($meta ? "$meta — " : '') . $note),
            'cost_min' => $min,
            'cost_max' => $max,
            'is_default_pick' => $pick,
        ];
    }

    /** OpenStreetMap embed centred on a point (same shape the Tokyo sample uses). */
    protected function osm(float $lat, float $lon, float $span = 0.03): string
    {
        $b = [$lon - $span, $lat - $span * 0.6, $lon + $span, $lat + $span * 0.6];

        return 'https://www.openstreetmap.org/export/embed.html?bbox=' . implode('%2C', array_map(fn ($v) => round($v, 4), $b))
            . "&layer=mapnik&marker={$lat}%2C{$lon}";
    }

    /** Generic outfit inspiration (no faces), shared with the Tokyo sample. */
    protected function outfitIdeas(): array
    {
        return [
            ['url' => 'https://images.pexels.com/photos/934063/pexels-photo-934063.jpeg?auto=compress&cs=tinysrgb&w=600', 'label' => 'Idea 1', 'alt' => 'Striped top, jeans, sunglasses'],
            ['url' => 'https://images.pexels.com/photos/28645956/pexels-photo-28645956.jpeg?auto=compress&cs=tinysrgb&w=600', 'label' => 'Idea 2', 'alt' => 'Sneakers, sunglasses, tote bag'],
            ['url' => 'https://images.pexels.com/photos/3944690/pexels-photo-3944690.jpeg?auto=compress&cs=tinysrgb&w=600', 'label' => 'Idea 3', 'alt' => 'Jeans, scarf, sneakers'],
        ];
    }
}
