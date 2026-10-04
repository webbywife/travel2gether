<?php

namespace App\Http\Controllers;

use App\Support\Gallery;
use Illuminate\View\View;

/** Phase 8 — "From my travels": Lea's own photos, one page per place. */
class GalleryController extends Controller
{
    public function index(): View
    {
        abort_unless(Gallery::enabled(), 404);

        $places = Gallery::places();

        return view('gallery.index', [
            'places' => $places,
            'total' => array_sum(array_column($places, 'count')),
        ]);
    }

    public function show(string $slug): View
    {
        abort_unless(Gallery::enabled(), 404);

        $place = Gallery::place($slug);
        abort_if($place === null, 404);

        // The curated guide (best season, trip length) and PH-passport visa note, when we have them.
        $guide = \App\Support\Destinations::templateFor($place['name']);
        $visa = \App\Support\Destinations::visaForCountry($place['country']);

        return view('gallery.show', ['place' => $place, 'guide' => $guide, 'visa' => $visa]);
    }
}
