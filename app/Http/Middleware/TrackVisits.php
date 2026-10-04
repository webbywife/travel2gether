<?php

namespace App\Http\Middleware;

use App\Models\Trip;
use App\Support\VisitTracker;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Records the anonymous launch-funnel steps for guests (see VisitTracker). */
class TrackVisits
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->isMethod('GET') && ! $request->user() && ! $request->expectsJson() && $response->getStatusCode() === 200) {
            $event = match ($request->route()?->getName()) {
                'home' => 'landing',
                'register' => 'register',
                'gallery', 'gallery.show' => 'gallery',
                'trips.show' => ($t = $request->route('trip')) instanceof Trip && $t->created_by === null ? 'sample' : null,
                default => null,
            };
            if ($event) {
                VisitTracker::record($request, $event);
            }
        }

        return $response;
    }
}
