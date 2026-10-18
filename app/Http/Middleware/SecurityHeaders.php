<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Baseline security headers on every web response. */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $headers = [
            'X-Content-Type-Options' => 'nosniff',
            // The home page previews a trip in an iframe from our own origin; nobody else may frame us.
            'X-Frame-Options' => 'SAMEORIGIN',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), payment=(), usb=()',
            'Cross-Origin-Opener-Policy' => 'same-origin-allow-popups',
            // Structural CSP only: blocks plugins, <base> hijacking and foreign framing.
            // Scripts/styles are not restricted here (inline scripts + CDNs would need nonces first).
            'Content-Security-Policy' => "object-src 'none'; base-uri 'self'; frame-ancestors 'self'",
        ];
        if ($request->isSecure() && app()->environment('production')) {
            $headers['Strict-Transport-Security'] = 'max-age=31536000';
        }

        foreach ($headers as $k => $v) {
            if (! $response->headers->has($k)) {
                $response->headers->set($k, $v);
            }
        }

        return $response;
    }
}
