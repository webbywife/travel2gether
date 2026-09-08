<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        return view('marketing.home', [
            'sampleTrip' => $this->sampleTrip(),
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

        return view('dashboard', [
            'owned' => $owned,
            'shared' => $shared,
            'sampleTrip' => $this->sampleTrip(),
        ]);
    }

    private function sampleTrip(): ?Trip
    {
        return Trip::where('is_public', true)
            ->whereNull('created_by')
            ->orderByDesc('start_date')
            ->first();
    }
}
