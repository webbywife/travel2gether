<?php

namespace App\Jobs;

use App\Actions\IndexGeneratedPlaces;
use App\Models\TripDay;
use App\Services\GenerateDayItinerary;
use App\Services\StopPhotoFinder;
use App\Support\OsmMap;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

/**
 * Drafts one day with Gemini in the background (the call takes ~25-70s, too
 * long to hold a web request open). Replaces the day's stops/options on
 * success; on failure leaves them untouched and records a friendly message.
 */
class DraftDayWithAi implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 1;

    public int $timeout = 180;

    public function __construct(
        public int $dayId,
        public bool $countsAsRegeneration = false,
    ) {}

    public function handle(GenerateDayItinerary $ai, IndexGeneratedPlaces $index, StopPhotoFinder $photos): void
    {
        $day = TripDay::with('trip')->find($this->dayId);
        if (! $day) {
            return;
        }

        $day->update(['ai_status' => 'running']);

        try {
            $draft = $ai->forDay($day);
        } catch (\Throwable $e) {
            report($e);
            $day->update([
                'ai_status' => 'failed',
                'ai_error' => $e->getMessage() === 'quota'
                    ? 'The AI has hit its daily limit — try again tomorrow.'
                    : 'The AI draft didn\'t come through — try again in a moment.',
            ]);

            return;
        }

        DB::transaction(function () use ($day, $draft, $index) {
            $day->stops()->delete(); // cascades to options

            $lat = $day->lat ?? $day->trip->lat;
            $lon = $day->lon ?? $day->trip->lon;

            $day->update([
                'weather_note' => $draft['weather_note'] ?: $day->weather_note,
                'weather_tag' => $draft['weather_tag'],
                'outfit_chips' => $draft['outfit_chips'] ?: $day->outfit_chips,
                'summary' => $draft['summary'] ?: $day->summary,
                'hiccups' => $draft['hiccups'],
                'temp_high' => $draft['temp_high'] ?? $day->temp_high,
                'temp_low' => $draft['temp_low'] ?? $day->temp_low,
                // Backfill a default "today's area" map (keyless OSM embed) if the
                // day never got one — every AI-drafted day should be mappable.
                'map_embed_url' => $day->map_embed_url ?: (($lat && $lon) ? OsmMap::embedUrl((float) $lat, (float) $lon) : null),
                'source' => 'ai',
                'ai_status' => null,
                'ai_error' => null,
            ]);

            foreach ($draft['stops'] as $i => $stop) {
                $options = $stop['options'];
                unset($stop['options']);

                $record = $day->stops()->create($stop + [
                    'sort' => $i,
                    'has_options' => count($options) > 0,
                ]);

                foreach ($options as $k => $option) {
                    $record->options()->create($option + ['sort' => $k]);
                }

                $index($record->options);
            }

            // Only a successful *re*-draft by a non-admin spends the trip's free regeneration.
            if ($this->countsAsRegeneration) {
                $day->trip->increment('regenerations_used');
            }
        });

        // A picture per stop: Lea's gallery first, Pexels otherwise. Never blocks the draft.
        $area = $day->area_label ?: $day->title;
        foreach ($day->stops()->with('options')->get() as $stop) {
            if ($stop->options->isEmpty()) {
                continue; // transit / check-out rows
            }
            $pic = rescue(fn () => $photos->find(
                $stop->options->pluck('name')->all(), (string) $area, (string) $day->trip->destination), null);
            if ($pic) {
                $stop->update(['thumb_url' => $pic['url'], 'photo_source' => $pic['source'],
                    'photo_credit' => $pic['credit'], 'photo_credit_url' => $pic['credit_url']]);
            }
        }
    }

    /** Worker crashed / timed out — don't leave the day stuck on "drafting". */
    public function failed(?\Throwable $e): void
    {
        TripDay::whereKey($this->dayId)->update([
            'ai_status' => 'failed',
            'ai_error' => 'The AI draft timed out — try again in a moment.',
        ]);
    }
}
