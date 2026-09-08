<?php

namespace App\Http\Controllers;

use App\Actions\CreateTrip;
use App\Http\Requests\StoreTripRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TripBuilderController extends Controller
{
    public function create(): View
    {
        return view('trips.create', [
            'placesEnabled' => filled(config('services.google.maps_key')),
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
