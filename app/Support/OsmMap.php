<?php

namespace App\Support;

/**
 * A keyless "today's area" map embed via OpenStreetMap — the default map
 * every day gets (wizard skeleton or AI draft) before/without a Google Maps
 * key. Same embed style as the seeded Seoul/Tokyo samples.
 */
class OsmMap
{
    public static function embedUrl(float $lat, float $lon, float $radiusKm = 3.0): string
    {
        $dLat = $radiusKm / 111.0;
        $dLon = $radiusKm / (111.320 * max(cos(deg2rad($lat)), 0.15));

        $bbox = implode(',', [
            round($lon - $dLon, 5),
            round($lat - $dLat, 5),
            round($lon + $dLon, 5),
            round($lat + $dLat, 5),
        ]);

        return 'https://www.openstreetmap.org/export/embed.html'
            . '?bbox=' . urlencode($bbox)
            . '&layer=mapnik'
            . '&marker=' . urlencode(round($lat, 5) . ',' . round($lon, 5));
    }
}
