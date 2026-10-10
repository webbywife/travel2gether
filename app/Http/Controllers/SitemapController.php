<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Support\Destinations;
use App\Support\Gallery;
use Illuminate\Http\Response;

/** /sitemap.xml — every public page we want search engines to find. */
class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $urls = [
            [route('home'), '1.0'],
            [route('destinations'), '0.9'],
            [route('samples'), '0.9'],
        ];
        foreach (Destinations::templates() as $t) {
            $urls[] = [route('destinations.show', Destinations::slug($t)), '0.8'];
        }
        foreach (Trip::where('is_public', true)->whereNull('created_by')->get() as $trip) {
            $urls[] = [route('trips.show', $trip), '0.8'];   // the sample itineraries
        }
        if (Gallery::enabled()) {
            $urls[] = [route('gallery'), '0.6'];
            foreach (Gallery::places() as $p) {
                $urls[] = [route('gallery.show', $p['slug']), '0.5'];
            }
        }
        $urls[] = [route('privacy'), '0.2'];

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as [$loc, $priority]) {
            $xml .= '  <url><loc>' . e($loc) . '</loc><priority>' . $priority . '</priority></url>' . "\n";
        }
        $xml .= '</urlset>' . "\n";

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8', 'Cache-Control' => 'public, max-age=3600']);
    }
}
