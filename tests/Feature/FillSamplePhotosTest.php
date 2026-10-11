<?php

namespace Tests\Feature;

use App\Models\Trip;
use Database\Seeders\Kyoto2026Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FillSamplePhotosTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_fills_sample_stops_with_credited_photos_and_skips_transit(): void
    {
        config(['services.pexels.api_key' => 'test-key']);
        Http::fake(['api.pexels.com/*' => Http::response(['photos' => [[
            'src' => ['medium' => 'https://images.pexels.com/photos/1/x.jpeg'],
            'photographer' => 'Ana', 'url' => 'https://www.pexels.com/photo/1/',
        ]]])]);
        $this->seed(Kyoto2026Seeder::class);

        $this->artisan('samples:photos')->assertSuccessful();

        $stops = Trip::where('slug', 'kyoto-2026')->sole()->stops()->get();
        $this->assertGreaterThan(5, $stops->whereNotNull('thumb_url')->count());
        $this->assertNull($stops->firstWhere('title', 'Land at Kansai Airport (KIX)')->thumb_url);
        $this->assertNotNull($stops->whereNotNull('thumb_url')->first()->photo_credit);

        $this->get('/t/kyoto-2026')->assertOk()->assertSee('class="stop-photo"', false);
    }

    public function test_a_rate_limited_search_is_not_cached_so_a_rerun_can_fill_it(): void
    {
        config(['services.pexels.api_key' => 'test-key']);
        Http::fake(['api.pexels.com/*' => Http::sequence()->push([], 429)->whenEmpty(Http::response(['photos' => [[
            'src' => ['medium' => 'https://images.pexels.com/photos/2/y.jpeg'], 'photographer' => 'Ben', 'url' => 'https://www.pexels.com/photo/2/',
        ]]]))]);
        $this->seed(Kyoto2026Seeder::class);

        $this->artisan('samples:photos', ['--max-calls' => 1])->assertSuccessful();
        $this->artisan('samples:photos')->assertSuccessful();

        $this->assertGreaterThan(5, Trip::where('slug', 'kyoto-2026')->sole()->stops()->whereNotNull('thumb_url')->count());
    }
}
