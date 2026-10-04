<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Counts the launch funnel per source — landing → sample → register → signup —
 * so we can see whether TikTok or Instagram actually sends people who stay.
 * Anonymous: the visitor is a one-way hash of the session id, nothing else.
 */
class VisitTracker
{
    public const SOURCES = ['tiktok', 'ig', 'fb', 'threads', 'x', 'google', 'direct', 'other'];

    private const BOTS = '/bot|crawl|spider|slurp|preview|facebookexternalhit|meta-externalagent|embedly|curl|wget|python|go-http|headless|lighthouse/i';

    public static function record(Request $request, string $event): void
    {
        if (! $request->hasSession() || preg_match(self::BOTS, (string) $request->userAgent())) {
            return;
        }

        rescue(fn () => DB::table('visit_events')->insertOrIgnore([
            'day' => now()->toDateString(),
            'source' => self::source($request),
            'event' => $event,
            'visitor' => substr(hash_hmac('sha256', $request->session()->getId(), (string) config('app.key')), 0, 16),
            'mobile' => (bool) preg_match('/iPhone|iPad|Android|Mobile/i', (string) $request->userAgent()),
            'created_at' => now(),
        ]), report: false);
    }

    /** Where this visitor came from — remembered for the session so later steps keep the first source. */
    public static function source(Request $request): string
    {
        $session = $request->session();
        $ref = strtolower((string) $request->query('ref', ''));
        $ref = ['instagram' => 'ig', 'facebook' => 'fb', 'messenger' => 'fb', 'twitter' => 'x'][$ref] ?? $ref;

        if (in_array($ref, self::SOURCES, true)) {
            $session->put('t2g_src', $ref);
        }

        if (! $session->has('t2g_src')) {
            $session->put('t2g_src', self::guess((string) $request->userAgent(), (string) $request->headers->get('referer')));
        }

        return $session->get('t2g_src');
    }

    private static function guess(string $ua, string $referer): string
    {
        $host = strtolower((string) parse_url($referer, PHP_URL_HOST));

        return match (true) {
            (bool) preg_match('/musical_ly|BytedanceWebview|TikTok/i', $ua), str_contains($host, 'tiktok') => 'tiktok',
            (bool) preg_match('/Instagram/i', $ua), str_contains($host, 'instagram') => 'ig',
            (bool) preg_match('/Barcelona/i', $ua), str_contains($host, 'threads') => 'threads',
            (bool) preg_match('/FBAN|FBAV|FB_IAB/i', $ua), str_contains($host, 'facebook'), str_contains($host, 'fb.me') => 'fb',
            str_contains($host, 't.co'), str_contains($host, 'x.com'), str_contains($host, 'twitter') => 'x',
            str_contains($host, 'google.') => 'google',
            $host === '' || str_contains($host, 'travel2gether') => 'direct',
            default => 'other',
        };
    }
}
