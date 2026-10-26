<?php

namespace App\Actions;

use App\Models\Trip;
use App\Models\TripDay;
use Illuminate\Support\Facades\DB;

/**
 * "Clear day" / "Reset itinerary": a middle day goes back to its blank
 * morning / lunch / afternoon / dinner slots. Its date, area and hotel stay;
 * stops, options and everyone's picks on them go (picks cascade with the stops).
 * Arrival and departure days are never cleared — they hold the flight and hotel anchors.
 * A day that was AI-drafted stays marked as drafted, so clearing it doesn't
 * reset the trip's free re-draft allowance.
 */
class ClearTripDays
{
    /** @return int how many days were cleared */
    public function __invoke(Trip $trip, ?TripDay $only = null): int
    {
        $days = $trip->days()->orderBy('day_number')->get();
        $first = $days->first()?->id;
        $last = $days->last()?->id;

        $targets = $days->filter(fn (TripDay $d) => $d->id !== $first && $d->id !== $last
            && ! in_array($d->ai_status, ['queued', 'running'], true)
            && ($only === null || $d->id === $only->id));

        DB::transaction(function () use ($targets) {
            foreach ($targets as $day) {
                $name = $day->area_label ?: $day->title;
                $day->stops()->delete();
                foreach (CreateTrip::blankSlots($name) as $stop) {
                    $day->stops()->create($stop);
                }
                $day->update([
                    'summary' => "A day around {$name}. Fill the slots below, or let the draft-with-AI button suggest options.",
                    'hiccups' => [
                        'Check opening hours and weekly closing days the morning of — plenty of sites shut one weekday.',
                        'Have an indoor fallback for this area in case the forecast turns.',
                    ],
                    'ai_error' => null,
                ]);
            }
        });

        return $targets->count();
    }
}
