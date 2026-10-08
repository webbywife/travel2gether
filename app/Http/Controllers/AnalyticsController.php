<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\TripSegment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/** Admin-only (gated by the 'admin' Gate — see AppServiceProvider). */
class AnalyticsController extends Controller
{
    /**
     * No payment processing yet — this is the manual stand-in an admin uses
     * to flip a member's subscription until real billing exists.
     */
    public function toggleSubscription(User $user): RedirectResponse
    {
        $user->forceFill(['subscription' => $user->subscription === 'paid' ? 'free' : 'paid'])->save();

        return back()->with('status', "{$user->name} is now " . ($user->subscription === 'paid' ? 'a paid member.' : 'on the free tier.'));
    }

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
            'members' => User::withCount('ownedTrips')->orderBy('name')->get(),
            'totalSegments' => $totalSegments,
            'resolvedAirlinePct' => $totalSegments ? round($resolvedAirline / $totalSegments * 100) : 0,
            'resolvedAirportPct' => $totalSegments ? round($resolvedAirport / $totalSegments * 100) : 0,
            'topAirlines' => $topAirlines,
            'topRoutes' => $topRoutes,
            'topDepartureAirports' => $topDepartureAirports,
            'byMonth' => $byMonth,
            'topDestinations' => $topDestinations,
            'funnel' => $this->funnel(),
            'group' => $this->groupUse(),
        ]);
    }

    /**
     * Do groups actually use the group features? Members' own trips only:
     * the samples and the admins' trips are left out so testing doesn't count.
     *
     * @return array<string, int>
     */
    private function groupUse(): array
    {
        $adminIds = User::whereIn('email', config('app.admin_emails', []))->pluck('id');
        $trips = Trip::whereNotNull('created_by')->whereNotIn('created_by', $adminIds)->pluck('id');

        return [
            'trips' => $trips->count(),
            'invites' => \App\Models\TripInvite::whereIn('trip_id', $trips)->count(),
            'joined' => DB::table('trip_user')->whereIn('trip_id', $trips)->where('role', '!=', 'owner')->count(),
            'groupTrips' => DB::table('trip_user')->whereIn('trip_id', $trips)->where('role', '!=', 'owner')
                ->select('trip_id')->groupBy('trip_id')->havingRaw('count(*) >= 2')->get()->count(),
            'picks' => \App\Models\TripPick::whereIn('trip_id', $trips)->count(),
            'pickTrips' => \App\Models\TripPick::whereIn('trip_id', $trips)->whereNotNull('picked_by')
                ->select('trip_id')->groupBy('trip_id')->havingRaw('count(distinct picked_by) >= 2')->get()->count(),
            'hiccups' => DB::table('visit_events')->where('event', 'hiccups')
                ->where('day', '>=', now()->subDays(6)->toDateString())->count(),
        ];
    }

    /**
     * Launch funnel for the last 7 days: per source, how many visitors reached
     * each step (each visitor counts once per step per day).
     *
     * @return array<string, array<string, int>>
     */
    private function funnel(): array
    {
        $rows = \Illuminate\Support\Facades\DB::table('visit_events')
            ->where('day', '>=', now()->subDays(6)->toDateString())
            ->selectRaw('source, event, count(*) as n')
            ->groupBy('source', 'event')
            ->get();

        $out = [];
        foreach ($rows as $r) {
            $out[$r->source][$r->event] = (int) $r->n;
        }
        uasort($out, fn ($a, $b) => ($b['landing'] ?? 0) + ($b['sample'] ?? 0) <=> ($a['landing'] ?? 0) + ($a['sample'] ?? 0));

        return $out;
    }
}
