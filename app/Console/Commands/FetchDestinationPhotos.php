<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * One-time (re-runnable) job: pulls one cover photo per entry in
 * config/destination_templates.php from Pexels and saves it to
 * public/img/destinations/{slug}.jpg. Meant to be run locally — the app
 * never calls Pexels at runtime; the destinations page just serves
 * whatever landed in that folder, keyed by Str::slug($destination).
 *
 * Usage: php artisan destinations:fetch-photos [--force]
 */
class FetchDestinationPhotos extends Command
{
    protected $signature = 'destinations:fetch-photos {--force : Re-download even if a photo already exists}';

    protected $description = 'Download one Pexels cover photo per destination template into public/img/destinations/';

    public function handle(): int
    {
        $key = config('services.pexels.api_key');

        if (blank($key)) {
            $this->error('PEXELS_API_KEY is not set — add it to .env first.');

            return self::FAILURE;
        }

        $dir = public_path('img/destinations');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $templates = config('destination_templates', []);
        $this->info('Fetching photos for ' . count($templates) . ' destinations…');

        $ok = 0;
        $skipped = 0;
        $failed = [];

        foreach ($templates as $t) {
            $slug = Str::slug($t['destination']);
            $path = "{$dir}/{$slug}.jpg";

            if (file_exists($path) && ! $this->option('force')) {
                $skipped++;

                continue;
            }

            $query = "{$t['destination']} {$t['country']} travel landmark";

            // per_page=1 + orientation=landscape sometimes comes back with an
            // empty photos array despite a healthy total_results (a Pexels API
            // quirk, reproduced on "Madrid Spain travel landmark") — asking for
            // a few more and taking the first is the reliable workaround.
            $search = Http::withHeaders(['Authorization' => $key])
                ->get('https://api.pexels.com/v1/search', [
                    'query' => $query,
                    'per_page' => 3,
                    'orientation' => 'landscape',
                ]);

            if (! $search->successful()) {
                $failed[] = "{$t['destination']} (search {$search->status()})";

                continue;
            }

            $photoUrl = data_get($search->json(), 'photos.0.src.large2x')
                ?? data_get($search->json(), 'photos.0.src.large');

            if (! $photoUrl) {
                $failed[] = "{$t['destination']} (no results)";

                continue;
            }

            $image = Http::get($photoUrl);

            if (! $image->successful()) {
                $failed[] = "{$t['destination']} (download {$image->status()})";

                continue;
            }

            file_put_contents($path, $image->body());
            $this->line("✓ {$t['destination']} → img/destinations/{$slug}.jpg");
            $ok++;

            // Free tier is 200 req/hour — this is nowhere close, but stay polite.
            usleep(250_000);
        }

        $this->newLine();
        $this->info("Done — {$ok} downloaded, {$skipped} already had a photo.");

        if ($failed) {
            $this->warn('Could not fetch: ' . implode(', ', $failed));
        }

        return self::SUCCESS;
    }
}
