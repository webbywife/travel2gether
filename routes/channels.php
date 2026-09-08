<?php

use App\Models\Trip;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/**
 * Presence + edit stream for a single trip.
 *
 * Phase 2b will gate this by trip_user membership + role. For now the
 * trip owner and (for the shared sample) any signed-in viewer may join.
 */
Broadcast::channel('trip.{slug}', function ($user, string $slug) {
    $trip = Trip::where('slug', $slug)->first();

    if (! $trip) {
        return false;
    }

    if ($trip->created_by === $user->id || $trip->is_public) {
        return ['id' => $user->id, 'name' => $user->name, 'avatar' => $user->avatar ?? null];
    }

    return false;
});
