<?php

namespace App\Http\Controllers;

use App\Actions\DuplicateTrip;
use App\Models\Trip;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TripController extends Controller
{
    public function show(Request $request, Trip $trip): View
    {
        abort_unless($trip->canView($request->user()), 404);

        $trip->load([
            'days.stops.options',
            'budgetLines',
            'members',
            'invites',
        ]);

        return view('trips.show', [
            'trip' => $trip,
            'role' => $trip->roleFor($request->user()),
        ]);
    }

    public function duplicate(Request $request, Trip $trip, DuplicateTrip $duplicate): RedirectResponse
    {
        $this->authorize('duplicate', $trip);

        $title = $request->string('title')->trim()->value()
            ?: "{$request->user()->name}'s {$trip->destination} trip";

        $copy = $duplicate($trip, $request->user(), $title);

        return redirect()
            ->route('trips.show', $copy)
            ->with('status', 'Trip copied — invite your group from the Share panel.');
    }
}
