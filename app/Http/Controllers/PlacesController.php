<?php

namespace App\Http\Controllers;

use App\Services\PlacesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlacesController extends Controller
{
    public function search(Request $request, PlacesService $places): JsonResponse
    {
        $data = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:120'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lon' => ['nullable', 'numeric', 'between:-180,180'],
            'kind' => ['nullable', 'in:area,hotel,destination'],
        ]);

        abort_unless($places->enabled(), 503, 'Place search is not configured.');

        return response()->json([
            'query' => $data['q'],
            'results' => $places->search(
                $data['q'],
                isset($data['lat']) ? (float) $data['lat'] : null,
                isset($data['lon']) ? (float) $data['lon'] : null,
                8,
                $data['kind'] ?? null,
            ),
        ]);
    }

    public function show(string $placeId, PlacesService $places): JsonResponse
    {
        abort_unless($places->enabled(), 503, 'Place search is not configured.');

        $place = $places->details($placeId);
        abort_if($place === null, 404);

        return response()->json($place);
    }

    /** "Near this area": airports, hotels and landmarks around a picked place. */
    public function explore(Request $request, PlacesService $places): JsonResponse
    {
        $data = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lon' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $result = $places->explore((float) $data['lat'], (float) $data['lon']);

        return response()->json($result ?? ['airports' => [], 'hotels' => [], 'landmarks' => [], 'areas' => [], 'partial' => true]);
    }
}
