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

    /** One destination guide — its own page so search engines can rank it ("Kyoto itinerary", "best time to visit Kyoto"). */
    public function show(string $slug): View
    {
        $t = Destinations::bySlug($slug);
        abort_if($t === null, 404);

        $related = collect(Destinations::templates())
            ->reject(fn ($o) => Destinations::slug($o) === $slug)
            ->sortBy(fn ($o) => [$o['country'] === $t['country'] ? 0 : 1, $o['continent'] === $t['continent'] ? 0 : 1])
            ->take(4)->values();

        return view('destinations.show', [
            't' => $t,
            'slug' => $slug,
            'visa' => Destinations::visaForCountry($t['country']),
            'gallery' => \App\Support\Gallery::enabled() ? \App\Support\Gallery::place($slug) : null,
            'related' => $related,
        ]);
    }
}
