<?php

namespace App\Http\Controllers;

use App\Actions\CreateTrip;
use App\Http\Requests\StoreTripRequest;
use App\Models\User;
use App\Support\Destinations;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TripBuilderController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        if ($limit = $this->limitReached($request)) {
            return $limit;
        }

        return view('trips.create', [
            'placesEnabled' => filled(config('services.google.maps_key')),
            'prefillDestination' => $request->query('destination', ''),
            'destinations' => Destinations::all(),
            'templates' => Destinations::templates(),
            'airports' => config('airports', []),
            'airlines' => config('airlines', []),
        ]);
    }

    public function store(StoreTripRequest $request, CreateTrip $create): RedirectResponse
    {
        if ($limit = $this->limitReached($request)) {
            return $limit;
        }

        $trip = $create($request->validated(), $request->user());

        return redirect()
            ->route('trips.show', $trip)
            ->with('status', 'Trip created. Fill in each day, invite your group, or draft a day with AI.');
    }

    /** Null when under the free-tier trip cap; otherwise a redirect to /upgrade. */
    private function limitReached(Request $request): ?RedirectResponse
    {
        if (! $request->user()->hasReachedTripLimit()) {
            return null;
        }

        return redirect()->route('upgrade')->with(
            'error',
            'Free members can have up to ' . User::FREE_TRIP_LIMIT . ' trips at a time — upgrade to add more.'
        );
    }
}
