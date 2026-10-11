<?php

namespace App\Console\Commands;

use App\Models\Trip;
use App\Services\StopPhotoFinder;
use Illuminate\Console\Command;

/**
 * Gives the sample trips' stops a picture, the same way AI drafts get one:
 * Lea's gallery first, Pexels otherwise. Safe to re-run — it only fills stops
 * without a photo, and stops early to stay under Pexels' 200 requests/hour.
 */
class FillSamplePhotos extends Command
{
    protected $signature = 'samples:photos {--max-calls=150 : Pexels searches to spend this run}';

    protected $description = 'Add a photo to each sample-trip stop that has none (gallery first, then Pexels)';

    /** Getting there, not a place: no photo. */
    private const TRANSIT = '/\b(land|landing|airport|fly|flight|train|transfer|check[- ]?(in|out)|bus|taxi|shuttle|depart|head (back|home)|drop bags|ferry)\b/i';

    public function handle(): int
    {
        $budget = (int) $this->option('max-calls');
        $spent = $filled = $missed = 0;

        $samples = Trip::whereNull('created_by')->where('is_public', true)->with('days.stops.options')->get();
        foreach ($samples as $trip) {
            foreach ($trip->days as $day) {
                $finder = new StopPhotoFinder;
                $area = (string) ($day->area_label ?: $day->title);
                foreach ($day->stops as $stop) {
                    if ($stop->thumb_url || preg_match(self::TRANSIT, (string) $stop->title)) {
                        continue;
                    }
                    if ($spent + $finder->pexelsCalls() >= $budget) {
                        $this->warn("Stopped at the Pexels budget ({$budget}); run again in an hour for the rest.");
                        break 3;
                    }
                    $names = $stop->options->pluck('name')->push($stop->title)->all();
                    $pic = rescue(fn () => $finder->find($names, $area, (string) $trip->destination), null);
                    if ($pic) {
                        $stop->update(['thumb_url' => $pic['url'], 'photo_source' => $pic['source'],
                            'photo_credit' => $pic['credit'], 'photo_credit_url' => $pic['credit_url']]);
                        $filled++;
                    } else {
                        $missed++;
                    }
                }
                $spent += $finder->pexelsCalls();
            }
        }

        $this->info("Filled {$filled} stops, {$missed} without a match, {$spent} Pexels searches.");

        return self::SUCCESS;
    }
}
