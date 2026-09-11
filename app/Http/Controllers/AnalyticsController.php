<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\TripSegment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/** Admin-only (gated by the 'admin' Gate — see AppServiceProvider). */
class AnalyticsController extends Controller
{
    public function index(): View
    {
        $topAirlines = TripSegment::query()
            ->whereNotNull('airline_code')
            ->select('airline_code', 'airline_name', DB::raw('count(*) as uses'))
            ->groupBy('airline_code', 'airline_name')
            ->orderByDesc('uses')
            ->limit(10)
            ->get();

        $topRoutes = TripSegment::query()
            ->whereNotNull('from_code')->whereNotNull('to_code')
            ->select('from_code', 'to_code', DB::raw('count(*) as uses'))
            ->groupBy('from_code', 'to_code')
            ->orderByDesc('uses')
            ->limit(10)
            ->get();

        $topDepartureAirports = TripSegment::query()
            ->whereNotNull('from_code')
            ->select('from_code', DB::raw('count(*) as uses'))
            ->groupBy('from_code')
            ->orderByDesc('uses')
            ->limit(10)
            ->get();

        // How many segments resolved to a known code vs. stayed free text —
        // tells us whether the curated lists are actually covering what
        // people type, or need more entries.
        $totalSegments = TripSegment::count();
        $resolvedAirline = TripSegment::whereNotNull('airline_code')->count();
        $resolvedAirport = TripSegment::whereNotNull('from_code')->count();

        // Grouped in PHP rather than a DB::raw date-format function — those
        // differ by driver (MySQL vs. SQLite in tests) and this dataset is
        // small enough that it doesn't matter.
        $byMonth = Trip::query()->pluck('start_date')
            ->groupBy(fn ($date) => $date->format('Y-m'))
            ->map(fn ($group) => $group->count())
            ->sortKeys();

        $topDestinations = Trip::query()
            ->select('destination', DB::raw('count(*) as uses'))
            ->groupBy('destination')
            ->orderByDesc('uses')
            ->limit(10)
            ->get();

        return view('analytics.index', [
            'totalTrips' => Trip::count(),
            'totalUsers' => User::count(),
            'totalSegments' => $totalSegments,
            'resolvedAirlinePct' => $totalSegments ? round($resolvedAirline / $totalSegments * 100) : 0,
            'resolvedAirportPct' => $totalSegments ? round($resolvedAirport / $totalSegments * 100) : 0,
            'topAirlines' => $topAirlines,
            'topRoutes' => $topRoutes,
            'topDepartureAirports' => $topDepartureAirports,
            'byMonth' => $byMonth,
            'topDestinations' => $topDestinations,
        ]);
    }
}
