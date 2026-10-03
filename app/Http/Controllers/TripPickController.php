<?php

namespace App\Http\Controllers;

use App\Events\TripActivity;
use App\Models\Stop;
use App\Models\Trip;
use App\Models\TripPick;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Phase 2b — the group's picks, stored server-side so every member sees the
 * same choices. Owners/editors choose; viewers (and anyone who can view a
 * public trip) can read them.
 */
class TripPickController extends Controller
{
    /** Current picks for the trip, keyed by stop id — polled by the itinerary page. */
    public function index(Request $request, Trip $trip): JsonResponse
    {
        abort_unless($trip->canView($request->user()), 404);

        return response()->json(['picks' => $this->picksFor($trip)]);
    }

    public function update(Request $request, Trip $trip, Stop $stop): JsonResponse
    {
        abort_unless($trip->canView($request->user()), 404);
        abort_unless($trip->canPick($request->user()), 403);
        $this->ensureStopBelongsTo($trip, $stop);

        $data = $request->validate([
            'option_id' => ['required', 'integer'],
        ]);

        $option = $stop->options()->whereKey($data['option_id'])->first();
        if (! $option) {
            return response()->json(['message' => 'That option is not part of this stop.'], 422);
        }

        $pick = TripPick::updateOrCreate(
            ['trip_id' => $trip->id, 'stop_id' => $stop->id],
            ['stop_option_id' => $option->id, 'picked_by' => $request->user()->id],
        );
        $pick->setRelation('picker', $request->user());

        $this->broadcast($trip, $request, ['stopId' => $stop->id] + $pick->toClient());

        return response()->json(['stopId' => $stop->id] + $pick->toClient());
    }

    /** Clear the group's pick so the stop falls back to its default option. */
    public function destroy(Request $request, Trip $trip, Stop $stop): JsonResponse
    {
        abort_unless($trip->canView($request->user()), 404);
        abort_unless($trip->canPick($request->user()), 403);
        $this->ensureStopBelongsTo($trip, $stop);

        TripPick::where('trip_id', $trip->id)->where('stop_id', $stop->id)->delete();

        $this->broadcast($trip, $request, ['stopId' => $stop->id, 'optionId' => null]);

        return response()->json(['stopId' => $stop->id, 'optionId' => null]);
    }

    /** @return array<int, array<string, mixed>> */
    private function picksFor(Trip $trip): array
    {
        return $trip->picks()->with('picker:id,name')->get()
            ->mapWithKeys(fn (TripPick $p) => [$p->stop_id => $p->toClient()])
            ->all();
    }

    private function ensureStopBelongsTo(Trip $trip, Stop $stop): void
    {
        abort_unless($stop->day()->where('trip_id', $trip->id)->exists(), 404);
    }

    /** @param  array<string, mixed>  $payload */
    private function broadcast(Trip $trip, Request $request, array $payload): void
    {
        // Live push only when Reverb is configured; the page also polls, so
        // nothing breaks while broadcasting is off.
        if (config('broadcasting.default') === 'reverb') {
            rescue(fn () => TripActivity::dispatch($trip, 'pick', $payload, $request->user()->id, $request->user()->name), report: false);
        }
    }
}
