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

        $trip->update([
            'title' => ($data['title'] ?? '') ?: "{$trip->owner->name}'s {$data['destination']} trip",
            'destination' => $data['destination'],
            'party_size' => $data['party_size'] ?? $trip->party_size,
            'currency' => $data['currency'] ?? $trip->currency,
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
}
