<?php

namespace App\Services;

use App\Support\Gallery;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * A picture for each AI-drafted stop. Lea's own gallery comes first — a photo
 * whose caption names the same place. Only when none fits does it fall back
 * to Pexels (free, credited per Pexels' guidelines).
 */
class StopPhotoFinder
{
    private const PEXELS_URL = 'https://api.pexels.com/v1/search';

    /** Pexels allows 200 requests/hour; a draft never needs more than this. */
    private const MAX_PEXELS_PER_DAY = 8;

    private int $pexelsCalls = 0;

    /** @var array<int, array{key: string, slug: string, photo: array<string, mixed>}>|null */
    private ?array $galleryIndex = null;

    /**
     * @param  array<int, string>  $names  candidate place names, best first (e.g. the stop's options)
     * @return array{url: string, source: string, credit: ?string, credit_url: ?string}|null
     */
    public function find(array $names, string $area, string $destination): ?array
    {
        $names = array_values(array_filter(array_map(fn ($n) => trim((string) $n), $names)));
        if (! $names) {
            return null;
        }

        return $this->fromGallery($names) ?? $this->fromPexels($names, $area, $destination);
    }

    /** @param  array<int, string>  $names */
    private function fromGallery(array $names): ?array
    {
        $index = $this->galleryIndex ??= $this->buildGalleryIndex();
        if (! $index) {
            return null;
        }

        foreach ($names as $name) {
            $want = self::norm($name);
            foreach ($index as $entry) {
                if (self::samePlace($want, $entry['key'])) {
                    return [
                        'url' => Gallery::thumb($entry['slug'], $entry['photo']),
                        'source' => 'gallery',
                        'credit' => 'From my travels',
                        'credit_url' => route('gallery.show', $entry['slug']),
                    ];
                }
            }
        }

        return null;
    }

    /** Captioned stills, landscape first (they crop better into a stop thumbnail). */
    private function buildGalleryIndex(): array
    {
        $out = [];
        foreach (Gallery::places() as $place) {
            foreach ($place['photos'] as $ph) {
                if ($ph['type'] !== 'photo' || trim($ph['place']) === '') {
                    continue;
                }
                $out[] = ['key' => self::norm($ph['place']), 'slug' => $place['slug'], 'photo' => $ph,
                    'landscape' => $ph['w'] >= $ph['h']];
            }
        }
        usort($out, fn ($a, $b) => $b['landscape'] <=> $a['landscape']);

        return $out;
    }

    /** @param  array<int, string>  $names */
    private function fromPexels(array $names, string $area, string $destination): ?array
    {
        $key = config('services.pexels.api_key');
        if (blank($key)) {
            return null;
        }

        $city = trim(Str::before($destination, ','));
        $queries = array_unique(array_filter([
            $names[0] . ' ' . $city,
            isset($names[1]) ? $names[1] . ' ' . $city : null,
            $area && $area !== $names[0] ? $area . ' ' . $city : null,
        ]));

        foreach ($queries as $q) {
            if ($this->pexelsCalls >= self::MAX_PEXELS_PER_DAY) {
                return null;
            }
            $hit = Cache::remember('pexels:stop:' . md5(Str::lower($q)), now()->addDays(30), function () use ($q, $key) {
                $this->pexelsCalls++;
                $res = rescue(fn () => Http::withHeaders(['Authorization' => $key])->timeout(8)
                    ->get(self::PEXELS_URL, ['query' => $q, 'per_page' => 1, 'orientation' => 'landscape']), null, false);
                $p = $res?->successful() ? ($res->json('photos.0') ?? null) : null;

                return $p ? [
                    'url' => (string) data_get($p, 'src.medium'),
                    'credit' => 'Photo: ' . data_get($p, 'photographer', 'Pexels') . ' / Pexels',
                    'credit_url' => (string) data_get($p, 'url'),
                ] : false; // cache misses too, so we don't re-ask
            });

            if ($hit && str_starts_with($hit['url'], 'https://images.pexels.com/')) {
                return $hit + ['source' => 'pexels'];
            }
        }

        return null;
    }

    private static function norm(string $s): string
    {
        $s = Str::lower(Str::ascii($s));
        $s = preg_replace('/[^a-z0-9 ]+/', ' ', $s);

        return trim(preg_replace('/\s+/', ' ', $s));
    }

    /** "Golden Gate Bridge Welcome Center" ≈ "Golden Gate Bridge"; short/generic words don't count. */
    private static function samePlace(string $a, string $b): bool
    {
        if ($a === '' || $b === '') {
            return false;
        }
        [$short, $long] = strlen($a) <= strlen($b) ? [$a, $b] : [$b, $a];
        if (strlen($short) < 6 || str_word_count($short) < 2 && strlen($short) < 8) {
            return false;
        }

        return str_contains(" {$long} ", " {$short} ");
    }
}
