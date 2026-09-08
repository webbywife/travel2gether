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

    /** The seeded public sample trips, ordered oldest first (Seoul, then Tokyo). */
    private function samples(): Collection
    {
        return Trip::where('is_public', true)
            ->whereNull('created_by')
            ->orderBy('id')
            ->get();
    }
}
