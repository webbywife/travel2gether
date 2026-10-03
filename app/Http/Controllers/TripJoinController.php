<?php

namespace App\Http\Controllers;

use App\Models\TripInvite;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TripJoinController extends Controller
{
    public function show(Request $request, TripInvite $invite): View|RedirectResponse
    {
        $invite->load('trip', 'creator');

        if (! $invite->isUsable()) {
            return view('trips.join', ['invite' => $invite, 'expired' => true]);
        }

        // Pinned-email invites must be accepted by that address.
        if ($invite->email && $request->user() && $request->user()->email !== $invite->email) {
            return view('trips.join', ['invite' => $invite, 'wrongAccount' => true]);
        }

        if ($request->user() && $invite->trip->isMember($request->user())) {
            return redirect()->route('trips.show', $invite->trip);
        }

        return view('trips.join', ['invite' => $invite]);
    }

    public function store(Request $request, TripInvite $invite): RedirectResponse
    {
        abort_unless($invite->isUsable(), 410, 'This invite link is no longer valid.');

        $user = $request->user();

        if ($invite->email && $user->email !== $invite->email) {
            abort(403, 'This invite is for a different email address.');
        }

        // Removed by the owner? Only an invite created after the removal brings them back.
        $removal = \Illuminate\Support\Facades\DB::table('trip_removals')
            ->where('trip_id', $invite->trip_id)->where('user_id', $user->id)->first();
        if ($removal) {
            abort_if($invite->created_at <= \Illuminate\Support\Carbon::parse($removal->removed_at), 403,
                'You were removed from this trip. Ask the owner for a new invite link.');
            \Illuminate\Support\Facades\DB::table('trip_removals')->where('id', $removal->id)->delete();
        }

        if (! $invite->trip->isMember($user)) {
            $invite->trip->members()->attach($user->id, [
                'role' => $invite->role,
                'invited_by' => $invite->created_by,
            ]);
            $invite->increment('uses');
        }

        return redirect()
            ->route('trips.show', $invite->trip)
            ->with('status', "You joined {$invite->trip->title} as {$invite->role}.");
    }
}
