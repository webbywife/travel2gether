<?php

namespace App\Http\Controllers;

use App\Models\Place;
use App\Models\PlaceRecommendation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlaceRecommendationController extends Controller
{
    /**
     * Thumbs up/down a place. Voting again with the same thumb clears the
     * vote; voting with the other thumb flips it. Signed-in users only —
     * enforced by the 'auth' middleware on the route.
     */
    public function store(Request $request, Place $place): JsonResponse
    {
        $data = $request->validate([
            'vote' => ['required', 'in:up,down'],
        ]);

        $vote = $data['vote'] === 'up' ? 1 : -1;
        $user = $request->user();

        $existing = PlaceRecommendation::where('place_id', $place->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing && $existing->vote === $vote) {
            $existing->delete();
            $myVote = null;
        } elseif ($existing) {
            $existing->update(['vote' => $vote]);
            $myVote = $vote;
        } else {
            PlaceRecommendation::create(['place_id' => $place->id, 'user_id' => $user->id, 'vote' => $vote]);
            $myVote = $vote;
        }

        return response()->json([
            'up' => $place->recommendations()->where('vote', 1)->count(),
            'down' => $place->recommendations()->where('vote', -1)->count(),
            'my_vote' => $myVote,
        ]);
    }
}
