<?php

namespace App\Support;

use App\Actions\CreateTrip;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * A guest's filled-in planner, held in their session until they sign up or
 * log in — then it becomes a real trip. Nothing is saved to the database (and
 * no AI runs) for guests; the payload was validated by StoreTripRequest.
 */
class PendingTrip
{
    private const KEY = 'pending_trip';

    public static function hold(Request $request, array $data): void
    {
        $request->session()->put(self::KEY, $data);
    }

    /** The held trip's destination, for "Save your Kyoto trip" on the auth pages. */
    public static function destination(Request $request): ?string
    {
        $data = $request->hasSession() ? $request->session()->get(self::KEY) : null;

        return is_array($data) ? (string) ($data['destination'] ?? '') : null;
    }

    /** Turns the held planner into the user's trip; null when there's none or they're at the free limit. */
    public static function claim(Request $request, User $user): ?Trip
    {
        $data = $request->session()->pull(self::KEY);

        if (! is_array($data) || $user->hasReachedTripLimit()) {
            return null;
        }

        return rescue(fn () => app(CreateTrip::class)($data, $user));
    }
}
