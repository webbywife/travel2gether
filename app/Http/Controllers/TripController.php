<?php

namespace App\Http\Controllers;

use App\Models\Trip;

class TripController extends Controller
{
    public function show(Trip $trip)
    {
        abort_unless($trip->is_public, 404);

        $trip->load([
            'days.stops.options',
            'budgetLines',
        ]);

        return view('trips.show', ['trip' => $trip]);
    }
}
