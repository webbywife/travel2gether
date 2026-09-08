<?php

namespace App\Http\Controllers;

use App\Actions\CreateTrip;
use App\Http\Requests\StoreTripRequest;
use App\Support\Destinations;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TripBuilderController extends Controller
{
    public function create(Request $request): View
    {
        return view('trips.create', [
            'placesEnabled' => filled(config('services.google.maps_key')),
            'prefillDestination' => $request->query('destination', ''),
            'destinations' => Destinations::all(),
        ]);
    }

    public function store(StoreTripRequest $request, CreateTrip $create): RedirectResponse
    {
        $trip = $create($request->validated(), $request->user());

        return redirect()
            ->route('trips.show', $trip)
            ->with('status', 'Trip created. Fill in each day, invite your group, or draft a day with AI.');
    }
}
