<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateTripDayRequest;
use App\Models\Trip;
use App\Models\TripDay;
use App\Services\GenerateDayItinerary;
use App\Jobs\DraftDayWithAi;
use App\Services\DayDetailsAssistant;
use App\Support\OsmMap;
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

        return view('trips.days.edit', [
            'trip' => $trip,
            'day' => $day,
            'aiHelper' => app(DayDetailsAssistant::class)->enabled(),
        ]);
    }

    public function update(UpdateTripDayRequest $request, Trip $trip, TripDay $day): RedirectResponse
    {
        abort_unless($day->trip_id === $trip->id, 404);

        $data = $request->validated();
        $picked = fn (string $a, string $b) => filled($data[$a] ?? null) && filled($data[$b] ?? null);

        // Area: new coordinates only from a picked place; clearing the area clears them;
        // editing other fields leaves the existing location alone.
        if (blank($data['area_label'] ?? null)) {
            $data['lat'] = $data['lon'] = null;
        } elseif (! $picked('lat', 'lon')) {
            unset($data['lat'], $data['lon']);
        }

        // Hotel: same rule; address comes from the picked place.
        if (blank($data['hotel_name'] ?? null)) {
            $data['hotel_address'] = $data['hotel_lat'] = $data['hotel_lon'] = null;
        } elseif (! $picked('hotel_lat', 'hotel_lon')) {
            unset($data['hotel_lat'], $data['hotel_lon'], $data['hotel_address']);
        }

        $locationChanged = array_key_exists('lat', $data)
            && ((string) $data['lat'] !== (string) $day->lat || (string) $data['lon'] !== (string) $day->lon);

        $day->fill($data);
        if ($locationChanged) {
            // Keep the day's "today's area" map in step with where the day now is.
            $lat = $day->lat ?? $trip->lat;
            $lon = $day->lon ?? $trip->lon;
            $day->map_embed_url = ($lat && $lon) ? OsmMap::embedUrl((float) $lat, (float) $lon) : $day->map_embed_url;
        }
        $day->save();

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

    /** "✨ Help me with this day" — AI suggestions for the Edit day form (nothing is saved here). */
    public function suggest(Request $request, Trip $trip, TripDay $day, DayDetailsAssistant $ai): JsonResponse
    {
        $this->authorize('update', $trip);
        abort_unless($day->trip_id === $trip->id, 404);
        abort_unless($ai->enabled(), 503, 'The AI helper is not configured.');

        $data = $request->validate([
            'wish' => ['nullable', 'string', 'max:200'],
            'avoid' => ['nullable', 'array', 'max:6'],
            'avoid.*' => ['string', 'max:120'],
        ]);

        try {
            return response()->json($ai->suggest($day, $data['wish'] ?? null, $data['avoid'] ?? []));
        } catch (\RuntimeException $e) {
            return response()->json(['message' => match ($e->getMessage()) {
                'quota' => 'The AI is busy — try again in a few seconds.',
                default => 'The AI couldn\'t come up with suggestions just now — try again in a moment.',
            }], 503);
        }
    }

    /** Polled by the itinerary page while a day is being drafted. */
    public function aiStatus(Request $request, Trip $trip, TripDay $day): JsonResponse
    {
        abort_unless($trip->canView($request->user()) && $day->trip_id === $trip->id, 404);

        return response()->json(['status' => $day->ai_status ?? 'done', 'error' => $day->ai_error]);
    }
}
