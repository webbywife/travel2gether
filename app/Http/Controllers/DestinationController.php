<?php

namespace App\Http\Controllers;

use App\Support\Destinations;
use Illuminate\View\View;

class DestinationController extends Controller
{
    public function index(): View
    {
        return view('destinations.index', [
            'destinations' => Destinations::all(),
            'templates' => Destinations::templates(),
            'categories' => Destinations::categories(),
        ]);
    }
}
