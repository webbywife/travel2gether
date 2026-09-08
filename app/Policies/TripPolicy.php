<?php

namespace App\Policies;

use App\Models\Trip;
use App\Models\User;

class TripPolicy
{
    public function view(?User $user, Trip $trip): bool
    {
        return $trip->canView($user);
    }

    public function update(User $user, Trip $trip): bool
    {
        return $trip->canEdit($user);
    }

    public function manageMembers(User $user, Trip $trip): bool
    {
        return $trip->roleFor($user) === 'owner';
    }

    public function delete(User $user, Trip $trip): bool
    {
        return $trip->roleFor($user) === 'owner';
    }

    /** Anyone signed in may fork a trip they can view. */
    public function duplicate(User $user, Trip $trip): bool
    {
        return $trip->canView($user);
    }
}
