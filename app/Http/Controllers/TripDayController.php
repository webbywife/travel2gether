<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateTripDayRequest;
use App\Models\Trip;
use App\Models\TripDay;
use App\Services\GenerateDayItinerary;
use App\Jobs\DraftDayWithAi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function generate(Request $request, Trip $trip, TripDay $day, GenerateDayItinerary $ai): RedirectResponse
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

        if (in_array($day->ai_status, ['queued', 'running'], true)) {
            return back()->with('status', "{$day->title} is already being drafted — it'll appear in a moment.")
                ->withFragment((string) $day->day_number);
        }

        // The model call takes ~25-70s, so it runs on the queue; the page polls
        // aiStatus() and reloads when the draft lands.
        $day->update(['ai_status' => 'queued', 'ai_error' => null]);
        DraftDayWithAi::dispatch($day->id, $isRegeneration && ! $request->user()->isAdmin());

        return back()->with('status', "Drafting {$day->title} with AI — this takes about a minute. The page will update by itself.")
            ->withFragment((string) $day->day_number);
    }

    /** Polled by the itinerary page while a day is being drafted. */
    public function aiStatus(Request $request, Trip $trip, TripDay $day): JsonResponse
    {
        abort_unless($trip->canView($request->user()) && $day->trip_id === $trip->id, 404);

        return response()->json(['status' => $day->ai_status ?? 'done', 'error' => $day->ai_error]);
    }
}
