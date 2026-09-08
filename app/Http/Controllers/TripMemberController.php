<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\TripInvite;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TripMemberController extends Controller
{
    /** Owner creates a shareable invite link with a role attached. */
    public function storeInvite(Request $request, Trip $trip): RedirectResponse
    {
        $this->authorize('manageMembers', $trip);

        $data = $request->validate([
            'role' => ['required', 'in:editor,viewer'],
            'email' => ['nullable', 'email', 'max:255'],
            'expires_in_days' => ['nullable', 'integer', 'between:1,90'],
        ]);

        $trip->invites()->create([
            'role' => $data['role'],
            'email' => $data['email'] ?? null,
            'created_by' => $request->user()->id,
            'expires_at' => isset($data['expires_in_days'])
                ? now()->addDays($data['expires_in_days'])
                : null,
        ]);

        return back()->with('status', 'Invite link created.');
    }

    public function revokeInvite(Request $request, Trip $trip, TripInvite $invite): RedirectResponse
    {
        $this->authorize('manageMembers', $trip);
        abort_unless($invite->trip_id === $trip->id, 404);

        $invite->update(['revoked_at' => now()]);

        return back()->with('status', 'Invite link revoked.');
    }

    public function updateRole(Request $request, Trip $trip, User $user): RedirectResponse
    {
        $this->authorize('manageMembers', $trip);

        $data = $request->validate(['role' => ['required', 'in:editor,viewer']]);

        abort_if($user->id === $trip->created_by, 403, 'The owner role cannot be changed.');

        $trip->members()->updateExistingPivot($user->id, ['role' => $data['role']]);

        return back()->with('status', 'Role updated.');
    }

    public function destroy(Request $request, Trip $trip, User $user): RedirectResponse
    {
        $this->authorize('manageMembers', $trip);

        abort_if($user->id === $trip->created_by, 403, 'The owner cannot be removed.');

        $trip->members()->detach($user->id);

        return back()->with('status', 'Collaborator removed.');
    }

    /** A collaborator removes themselves. */
    public function leave(Request $request, Trip $trip): RedirectResponse
    {
        abort_if($request->user()->id === $trip->created_by, 403, 'The owner cannot leave their own trip.');
        abort_unless($trip->isMember($request->user()), 404);

        $trip->members()->detach($request->user()->id);

        return redirect()->route('dashboard')->with('status', "You left {$trip->title}.");
    }
}
