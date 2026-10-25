<?php

namespace App\Http\Controllers;

use App\Actions\DuplicateTrip;
use App\Http\Requests\UpdateTripRequest;
use App\Models\Trip;
use App\Support\Destinations;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TripController extends Controller
{
    public function show(Request $request, Trip $trip): View
    {
        abort_unless($trip->canView($request->user()), 404);

        $trip->load([
            'days.stops.options.place.recommendations',
            'budgetLines',
            'members',
            'invites',
            'picks.picker:id,name',
        ]);

        return view('trips.show', [
            'trip' => $trip,
            'role' => $trip->roleFor($request->user()),
            'picks' => $trip->picks->keyBy('stop_id'),
            'canPick' => $trip->canPick($request->user()),
            'aiEnabled' => app(\App\Services\GenerateDayItinerary::class)->enabled(),
            'phpRate' => app(\App\Services\ExchangeRates::class)->rate($trip->currency ?: 'USD', 'PHP'),
        ]);
    }

    /** A printable, scrapbook-styled export of the finished itinerary. */
    public function print(Request $request, Trip $trip): View
    {
        abort_unless($trip->canView($request->user()), 404);

        $trip->load([
            'days.stops.options.place.recommendations',
            'budgetLines',
        ]);

        $favorites = $trip->days
            ->flatMap(fn ($day) => $day->stops)
            ->flatMap(fn ($stop) => $stop->options)
            ->filter(fn ($opt) => $opt->place && $opt->place->score() > 0)
            ->unique('place_id')
            ->sortByDesc(fn ($opt) => $opt->place->score())
            ->take(6)
            ->values();

        return view('trips.print', [
            'trip' => $trip,
            'favorites' => $favorites,
        ]);
    }

    /** Edit a trip's basic details (title, destination, hotel, party size). */
    public function edit(Request $request, Trip $trip): View
    {
        $this->authorize('update', $trip);

        return view('trips.edit', [
            'trip' => $trip,
            'destinations' => Destinations::all(),
            'templates' => Destinations::templates(),
        ]);
    }

    public function update(UpdateTripRequest $request, Trip $trip): RedirectResponse
    {
        $data = $request->validated();

        // A new currency converts the trip's amounts at today's rate (never just relabels them).
        $newCurrency = $data['currency'] ?? $trip->currency;
        if ($newCurrency !== $trip->currency && ! app(\App\Actions\ConvertTripCurrency::class)($trip, $newCurrency)) {
            return back()->withInput()->withErrors(['currency' => "Couldn't get today's exchange rate, so the currency wasn't changed. Please try again in a minute."]);
        }

        $trip->update([
            'title' => ($data['title'] ?? '') ?: "{$trip->owner->name}'s {$data['destination']} trip",
            'destination' => $data['destination'],
            'party_size' => $data['party_size'] ?? $trip->party_size,
            'hotel_name' => $data['hotel_name'] ?? null,
            'hotel_address' => $data['hotel_address'] ?? null,
        ]);

        return redirect()
            ->route('trips.show', $trip)
            ->with('status', 'Trip details updated.');
    }

    public function duplicate(Request $request, Trip $trip, DuplicateTrip $duplicate): RedirectResponse
    {
        $this->authorize('duplicate', $trip);
        $request->validate(['title' => ['nullable', 'string', 'max:120']]);

        if ($request->user()->hasReachedTripLimit()) {
            return redirect()->route('upgrade')->with('error',
                'Free members can have up to ' . \App\Models\User::FREE_TRIP_LIMIT . ' trips at a time — upgrade to add more.');
        }

        $title = $request->string('title')->trim()->value()
            ?: "{$request->user()->name}'s {$trip->destination} trip";

        $copy = $duplicate($trip, $request->user(), $title);

        return redirect()
            ->route('trips.show', $copy)
            ->with('status', 'Trip copied — invite your group from the Share panel.');
    }

    /** Pins for the trip-wide map: each day's hotel and the stops whose options we know the exact location of. */
    public function mapData(Request $request, Trip $trip): \Illuminate\Http\JsonResponse
    {
        abort_unless($trip->canView($request->user()), 404);
        $trip->load('days.stops.options.place');

        $days = $trip->days->map(function ($day) use ($trip) {
            $n = 0;
            $stops = [];
            foreach ($day->stops->sortBy('sort') as $stop) {
                if ($stop->options->isEmpty()) {
                    continue; // transit / checkout rows have nothing to pin
                }
                $opts = $stop->options->sortByDesc('is_default_pick');
                $at = $opts->first(fn ($o) => $o->place?->lat !== null && $o->place?->lon !== null);
                $stops[] = [
                    'n' => ++$n, 'id' => $stop->id, 'time' => $stop->time, 'title' => $stop->title,
                    'name' => ($at ?? $opts->first())->name,
                    'lat' => $at ? (float) $at->place->lat : null, 'lon' => $at ? (float) $at->place->lon : null,
                    'locate_url' => $at ? null : route('trips.stops.locate', [$trip, $stop->id]),
                ];
            }
            $hLat = $day->hotel_lat ?? $trip->hotel_lat ?? null;
            $hLon = $day->hotel_lon ?? $trip->hotel_lon ?? null;

            return [
                'day' => $day->day_number, 'title' => $day->title, 'date' => $day->date->format('D j M'),
                'hotel' => ($hLat && $hLon) ? ['name' => $day->hotel_name ?: $trip->hotel_name, 'lat' => (float) $hLat, 'lon' => (float) $hLon] : null,
                'essentials_url' => route('trips.days.essentials', [$trip, $day->id]),
                'stops' => $stops,
            ];
        })->values();

        return response()->json(['center' => $trip->lat && $trip->lon ? [(float) $trip->lat, (float) $trip->lon] : null, 'days' => $days]);
    }

    /** Finds one stop's exact spot by name (Photon, free), near the day's area; cached. Used by the trip map. */
    public function locateStop(Request $request, Trip $trip, \App\Models\Stop $stop, \App\Services\PlacesService $places): \Illuminate\Http\JsonResponse
    {
        abort_unless($trip->canView($request->user()) && $stop->day?->trip_id === $trip->id, 404);

        $day = $stop->day;
        $name = optional($stop->options()->orderByDesc('is_default_pick')->first())->name;
        $lat = (float) ($day->hotel_lat ?? $day->lat ?? $trip->lat);
        $lon = (float) ($day->hotel_lon ?? $day->lon ?? $trip->lon);
        if (! $name || ! $lat || ! $lon) {
            return response()->json(['found' => false]);
        }

        $hit = $places->locate($name, $lat, $lon);

        return response()->json($hit ? ['found' => true] + $hit : ['found' => false]);
    }

    /** "Use this itinerary": copy a sample onto the visitor's own dates. Guests are held until they sign up. */
    public function useSample(Request $request, Trip $trip, DuplicateTrip $duplicate): RedirectResponse
    {
        abort_unless($trip->isSample(), 404);
        $data = $request->validate(['arrival_date' => ['required', 'date', 'after_or_equal:today', 'before:+3 years']]);

        if (! $request->user()) {
            \App\Support\PendingTrip::holdCopy($request, $trip, $data['arrival_date']);
            \App\Support\VisitTracker::record($request, 'copy');

            return redirect()->route('register');
        }

        if ($request->user()->hasReachedTripLimit()) {
            return redirect()->route('upgrade')->with('error',
                'Free members can have up to ' . \App\Models\User::FREE_TRIP_LIMIT . ' trips at a time — upgrade to add more.');
        }

        $copy = $duplicate($trip, $request->user(), "{$request->user()->name}'s " . \Illuminate\Support\Str::before($trip->destination, ',') . ' trip', \Illuminate\Support\Carbon::parse($data['arrival_date']));

        return redirect()->route('trips.show', $copy)->with('status', 'Your trip is ready on your dates. Change anything, then invite your group from the Share panel.');
    }

    /** Owners can delete their own trip (frees a slot under the free-tier cap). Samples can't be deleted. */
    public function destroy(Request $request, Trip $trip): RedirectResponse
    {
        $this->authorize('delete', $trip);
        abort_if($trip->isSample(), 403);

        $request->validate(['confirm_title' => ['required', 'string']]);
        if (trim($request->input('confirm_title')) !== trim($trip->title)) {
            return back()->withErrors(['confirm_title' => 'Type the trip name exactly to confirm.']);
        }

        $title = $trip->title;
        $trip->delete(); // days, stops, options, picks, members and invites cascade

        return redirect()->route('dashboard')->with('status', "Deleted \"{$title}\".");
    }

    /** Beacon from the trip page: this visitor scrolled to a "Possible hiccups" box (counted once a day, anonymously). */
    public function seenHiccups(Request $request, Trip $trip): \Illuminate\Http\Response
    {
        if (! $request->user()?->isAdmin()) {
            \App\Support\VisitTracker::record($request, 'hiccups');
        }

        return response()->noContent();
    }
}
