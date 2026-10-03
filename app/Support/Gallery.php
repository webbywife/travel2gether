<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Phase 8 — Lea's own destination photos.
 *
 * The photos live on the public disk (storage/app/public/photos/<slug>/…),
 * uploaded with rsync, never committed. photos.json is the index the
 * curator tool writes: names + captions + month/year only — no coordinates.
 */
class Gallery
{
    private const INDEX = 'photos/photos.json';

    public static function enabled(): bool
    {
        return (bool) config('services.gallery.enabled');
    }

    /** @return array<int, array<string, mixed>> */
    public static function places(): array
    {
        if (! self::enabled()) {
            return []; // hides the nav link, home strip and destination covers too
        }

        $disk = Storage::disk('public');
        if (! $disk->exists(self::INDEX)) {
            return [];
        }

        // Re-read only when the index file changes (i.e. after an upload).
        $stamp = $disk->lastModified(self::INDEX);

        return Cache::remember('gallery:index:' . $stamp, now()->addDay(), function () use ($disk) {
            $data = json_decode((string) $disk->get(self::INDEX), true);

            return collect($data['places'] ?? [])
                ->filter(fn ($p) => is_array($p) && preg_match('/^[a-z0-9-]+$/', (string) ($p['slug'] ?? '')) && ! empty($p['photos']))
                ->map(fn ($p) => [
                    'slug' => $p['slug'],
                    'name' => (string) ($p['name'] ?? $p['slug']),
                    'country' => (string) ($p['country'] ?? ''),
                    'years' => array_values((array) ($p['years'] ?? [])),
                    'count' => count($p['photos']),
                    'photos' => collect($p['photos'])
                        ->filter(fn ($ph) => preg_match('/^[A-Za-z0-9._-]+\.jpe?g$/', (string) ($ph['file'] ?? '')))
                        ->map(fn ($ph) => [
                            'file' => $ph['file'],
                            'w' => (int) ($ph['w'] ?? 0),
                            'h' => (int) ($ph['h'] ?? 0),
                            'caption' => collect([$ph['place'] ?? null, $ph['city'] ?? null])->filter()->unique()->implode(' · '),
                            'taken' => (string) ($ph['taken'] ?? ''),
                        ])->values()->all(),
                ])
                ->values()->all();
        });
    }

    /** @return array<string, mixed>|null */
    public static function place(string $slug): ?array
    {
        return collect(self::places())->firstWhere('slug', $slug);
    }

    public static function has(string $slug): bool
    {
        return self::place($slug) !== null;
    }

    public static function url(string $slug, string $file, bool $thumb = false): string
    {
        return Storage::disk('public')->url("photos/{$slug}/" . ($thumb ? 't/' : '') . $file);
    }

    /** A cover for a place: a landscape shot from the middle of the set (rarely the first/test frame). */
    public static function cover(string $slug, bool $thumb = true): ?string
    {
        $place = self::place($slug);
        if (! $place) {
            return null;
        }
        $landscape = array_values(array_filter($place['photos'], fn ($p) => $p['w'] >= $p['h']));
        $pool = $landscape ?: $place['photos'];
        $pick = $pool[intdiv(count($pool), 2)];

        return self::url($slug, $pick['file'], $thumb);
    }

    /**
     * A spread of photos across places for the home page — one or two per
     * place, rotating daily so the strip changes.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function highlights(int $n = 10): array
    {
        $seed = (int) now()->format('Ymd');
        $out = [];
        foreach (self::places() as $i => $place) {
            $land = array_values(array_filter($place['photos'], fn ($p) => $p['w'] >= $p['h'])) ?: $place['photos'];
            $p = $land[($seed + $i * 7) % count($land)];
            $out[] = $p + ['slug' => $place['slug'], 'place' => $place['name']];
        }
        usort($out, fn ($a, $b) => crc32($a['file'] . $seed) <=> crc32($b['file'] . $seed));

        return array_slice($out, 0, $n);
    }
}
