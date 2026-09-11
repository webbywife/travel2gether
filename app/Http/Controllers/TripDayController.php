<?php

namespace App\Http\Controllers;

use App\Actions\IndexGeneratedPlaces;
use App\Http\Requests\UpdateTripDayRequest;
use App\Models\Trip;
use App\Models\TripDay;
use App\Services\GenerateDayItinerary;
use App\Support\OsmMap;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TripDayController extends Controller
{
    /** Edit one day's own details — title, area, hotel, notes. Stops/options aren't editable here. */
    public function edit(Trip $trip, TripDay $day): View
    {
        $this->authorize('update', $trip);
        abort_unless($day->trip_id === $trip->id, 404);

        return view('trips.days.edit', ['trip' => $trip, 'day' => $day]);
    }

    public function update(UpdateTripDayRequest $request, Trip $trip, TripDay $day): RedirectResponse
    {
        abort_unless($day->trip_id === $trip->id, 404);

        $day->update($request->validated());

        return redirect()->route('trips.show', $trip)
            ->withFragment((string) $day->day_number)
            ->with('status', "Updated {$day->title}.");
    }

    public function generate(Request $request, Trip $trip, TripDay $day, GenerateDayItinerary $ai, IndexGeneratedPlaces $index): RedirectResponse
    {
        $this->authorize('update', $trip);
        abort_unless($day->trip_id === $trip->id, 404);
        abort_unless($ai->enabled(), 503, 'AI drafting is not configured.');

        // The first draft of a day is always free; only *re*-drafting an
        // already-AI-drafted day counts against the trip's free regeneration.
        $isRegeneration = $day->source === 'ai';

        if ($isRegeneration && ! $trip->canRegenerate($request->user())) {
            return back()->with('error', 'You\'ve used your free re-draft for this trip. Upgrading lifts the limit.')
                ->with('paywall', true);
        }

        @set_time_limit(150); // the model call can run ~30-70s; nginx/FPM must allow it

        try {
            $draft = $ai->forDay($day);
        } catch (\Throwable $e) {
            report($e);

            $msg = $e->getMessage() === 'quota'
                ? 'The AI has hit its daily limit — try again tomorrow, or add billing to the Gemini project to lift it.'
                : 'The AI draft didn\'t come through — try again in a moment.';

            return back()->with('error', $msg);
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
        });

        // Only a *re*-draft by a non-admin spends the trip's free regeneration —
        // the first draft of a day is never counted, and admins are exempt
        // (so the sample trips can be redrafted freely).
        if ($isRegeneration && ! $request->user()->isAdmin()) {
            $trip->increment('regenerations_used');
        }

        return back()->with('status', "Drafted {$day->title} with AI — edit anything that's off.")
            ->withFragment((string) $day->day_number);
    }
}
