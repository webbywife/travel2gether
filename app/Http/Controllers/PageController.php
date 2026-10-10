<?php

namespace App\Http\Controllers;

use App\Models\Place;
use App\Models\Trip;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        $samples = $this->samples();

        return view('marketing.home', [
            'samples' => $samples,
            'sampleTrip' => $samples->first(),
        ]);
    }

    /** All the public sample itineraries, grouped by region. */
    public function sampleTrips(): View
    {
        $region = fn (Trip $t) => match (trim(\Illuminate\Support\Str::afterLast($t->destination, ','))) {
            'USA', 'United States' => 'USA',
            'France', 'Italy', 'Spain', 'United Kingdom', 'Germany', 'Greece' => 'Europe',
            default => 'Asia',
        };
        $samples = Trip::where('is_public', true)->whereNull('created_by')
            ->withCount('days')
            ->orderByRaw('slug = ? desc', ['tokyo-2026'])->orderBy('id')
            ->get();

        return view('samples.index', [
            'groups' => $samples->groupBy($region)->sortBy(fn ($g, $k) => array_search($k, ['Asia', 'USA', 'Europe'])),
        ]);
    }

    public function dashboard(Request $request): View
    {
        $user = $request->user();

        $owned = Trip::where('created_by', $user->id)
            ->withCount('members')
            ->latest()
            ->get();

        $shared = $user->trips()
            ->where('created_by', '!=', $user->id)
            ->withCount('members')
            ->latest('trip_user.created_at')
            ->get();

        $samples = $this->samples();

        return view('dashboard', [
            'owned' => $owned,
            'shared' => $shared,
            'samples' => $samples,
            'sampleTrip' => $samples->first(),
            'topPlaces' => Place::topRecommended(6)->get(),
        ]);
    }

    /** The seeded public sample trips — Tokyo first (the leisure trip), then Seoul. */
    private function samples(): Collection
    {
        return Trip::where('is_public', true)
            ->whereNull('created_by')
            ->orderByRaw('slug = ? desc', ['tokyo-2026'])
            ->orderBy('id')
            ->get();
    }
}
