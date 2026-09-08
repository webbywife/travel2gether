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
