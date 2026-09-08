<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\TripDay;
use App\Services\GenerateDayItinerary;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class TripDayController extends Controller
{
    public function generate(Trip $trip, TripDay $day, GenerateDayItinerary $ai): RedirectResponse
    {
        $this->authorize('update', $trip);
        abort_unless($day->trip_id === $trip->id, 404);
        abort_unless($ai->enabled(), 503, 'AI drafting is not configured.');

        @set_time_limit(150); // the model call can run ~30-70s; nginx/FPM must allow it

        try {
            $draft = $ai->forDay($day);
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'The AI draft didn\'t come through — try again in a moment.');
        }

        DB::transaction(function () use ($day, $draft) {
            $day->stops()->delete(); // cascades to options

            $day->update([
                'weather_note' => $draft['weather_note'] ?: $day->weather_note,
                'weather_tag' => $draft['weather_tag'],
                'outfit_chips' => $draft['outfit_chips'] ?: $day->outfit_chips,
                'summary' => $draft['summary'] ?: $day->summary,
                'hiccups' => $draft['hiccups'],
                'temp_high' => null,
                'temp_low' => null,
                'source' => 'ai',
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
            }
        });

        return back()->with('status', "Drafted {$day->title} with AI — edit anything that's off.")
            ->withFragment((string) $day->day_number);
    }
}
