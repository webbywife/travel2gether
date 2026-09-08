<?php

use App\Models\Trip;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/**
 * Presence + edit stream for a single trip. Only members of the trip
 * (owner / editor / viewer) may join and appear in the roster.
 */
Broadcast::channel('trip.{slug}', function ($user, string $slug) {
    $trip = Trip::where('slug', $slug)->first();

    if (! $trip || ! $trip->isMember($user)) {
        return false;
    }

    return [
        'id' => $user->id,
        'name' => $user->name,
        'avatar' => $user->avatar ?? null,
        'role' => $trip->roleFor($user),
    ];
});
