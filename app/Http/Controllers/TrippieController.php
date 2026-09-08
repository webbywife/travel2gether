<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Services\TrippieAssistant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrippieController extends Controller
{
    public function chat(Request $request, TrippieAssistant $trippie): JsonResponse
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:1200'],
            'history' => ['nullable', 'array', 'max:20'],
            'history.*.role' => ['required', 'in:user,model'],
            'history.*.text' => ['required', 'string', 'max:4000'],
            'trip_slug' => ['nullable', 'string', 'max:120'],
        ]);

        abort_unless($trippie->enabled(), 503, 'Trippie is away right now.');

        $context = $this->tripContext($data['trip_slug'] ?? null, $request->user());

        @set_time_limit(60);

        try {
            $result = $trippie->reply($data['message'], $data['history'] ?? [], $context);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'reply' => "Whoops, my map folded on me 🗺️ — give that another try in a sec.",
                'emotion' => 'worried',
            ]);
        }

        return response()->json($result);
    }

    /** @return array<string, mixed>|null */
    private function tripContext(?string $slug, $user): ?array
    {
        if (! $slug) {
            return null;
        }

        $trip = Trip::with('days:id,trip_id,area_label')->where('slug', $slug)->first();

        if (! $trip || ! $trip->canView($user)) {
            return null;
        }

        return array_filter([
            'destination' => $trip->destination,
            'dates' => $trip->start_date?->format('M j') . ' – ' . $trip->end_date?->format('M j, Y'),
            'travellers' => $trip->party_size,
            'hotel' => $trip->hotel_name,
            'areas' => $trip->days->pluck('area_label')->filter()->unique()->values()->all(),
            'interests' => $trip->interests['interests'] ?? null,
        ]);
    }
}
