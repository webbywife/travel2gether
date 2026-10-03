<?php

namespace Tests\Feature;

use App\Models\Trip;
use App\Models\TripDay;
use App\Models\User;
use App\Services\ClaudeClient;
use App\Services\StopPhotoFinder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;
use Tests\TestCase;

class StopPhotoAndContinuityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.gallery.enabled' => true, 'services.pexels.api_key' => 'test-pexels']);
        Storage::fake('public');
        Storage::disk('public')->put('photos/photos.json', json_encode(['places' => [[
            'slug' => 'san-francisco-bay-area', 'name' => 'San Francisco', 'country' => 'United States',
            'photos' => [['file' => 'ig-sf-001.jpg', 'w' => 1600, 'h' => 1200, 'place' => 'Golden Gate Bridge', 'city' => 'San Francisco', 'taken' => '2023-10']],
        ]]]));
    }

    private function pexels(string $photographer = 'Ana'): void
    {
        Http::fake(['api.pexels.com/*' => Http::response(['photos' => [[
            'url' => 'https://www.pexels.com/photo/lands-end-1/', 'photographer' => $photographer,
            'src' => ['medium' => 'https://images.pexels.com/photos/1/a.jpg?h=350'],
        ]]])]);
    }

    public function test_her_own_gallery_photo_wins_over_pexels(): void
    {
        $this->pexels();
        $pic = app(StopPhotoFinder::class)->find(['Golden Gate Bridge Welcome Center', 'Fort Point'], 'Presidio', 'San Francisco, USA');

        $this->assertSame('gallery', $pic['source']);
        $this->assertStringContainsString('photos/san-francisco-bay-area/t/ig-sf-001.jpg', $pic['url']);
        Http::assertNothingSent();
    }

    public function test_pexels_is_the_fallback_and_is_credited(): void
    {
        $this->pexels('Ana');
        $pic = app(StopPhotoFinder::class)->find(['Lands End Trail'], 'Outer Richmond', 'San Francisco, USA');

        $this->assertSame('pexels', $pic['source']);
        $this->assertSame('https://images.pexels.com/photos/1/a.jpg?h=350', $pic['url']);
        $this->assertSame('Photo: Ana / Pexels', $pic['credit']);
        Http::assertSent(fn ($r) => str_contains(urldecode($r->url()), 'Lands End Trail San Francisco') && $r->header('Authorization')[0] === 'test-pexels');
    }

    public function test_short_generic_names_never_match_a_gallery_caption(): void
    {
        Http::fake(['api.pexels.com/*' => Http::response(['photos' => []])]);
        $this->assertNull(app(StopPhotoFinder::class)->find(['Bridge'], '', 'San Francisco, USA'));
    }

    public function test_no_pexels_key_means_gallery_only(): void
    {
        config(['services.pexels.api_key' => null]);
        Http::fake();
        $this->assertNull(app(StopPhotoFinder::class)->find(['Lands End Trail'], '', 'San Francisco, USA'));
        Http::assertNothingSent();
    }

    public function test_drafts_start_from_last_nights_hotel_and_get_photos(): void
    {
        $this->pexels();
        $owner = User::factory()->create();
        $trip = Trip::create(['slug' => 'cal', 'title' => 'California', 'destination' => 'San Francisco, USA',
            'start_date' => '2026-10-31', 'end_date' => '2026-11-03', 'is_public' => false, 'created_by' => $owner->id,
            'hotel_name' => 'Hotel Zephyr', 'party_size' => 2]);
        $trip->members()->attach($owner->id, ['role' => 'owner']);
        $d1 = TripDay::create(['trip_id' => $trip->id, 'day_number' => 1, 'sort' => 1, 'date' => '2026-10-31', 'title' => 'Golden Gate', 'area_label' => 'San Francisco', 'hiccups' => []]);
        $d1->stops()->create(['sort' => 0, 'title' => 'Dinner at Fisherman\'s Wharf', 'has_options' => false]);
        $d2 = TripDay::create(['trip_id' => $trip->id, 'day_number' => 2, 'sort' => 2, 'date' => '2026-11-01', 'title' => 'Yosemite', 'area_label' => 'Yosemite Valley', 'hiccups' => []]);
        TripDay::create(['trip_id' => $trip->id, 'day_number' => 3, 'sort' => 3, 'date' => '2026-11-02', 'title' => 'Napa', 'area_label' => 'Napa Valley', 'hiccups' => []]);

        $this->mock(ClaudeClient::class, function (MockInterface $m) {
            $m->shouldReceive('enabled')->andReturn(true);
            $m->shouldReceive('json')->once()
                ->withArgs(fn ($system, $user) => str_contains($user, 'wakes up at Hotel Zephyr')
                    && str_contains($user, "ended at \"Dinner at Fisherman's Wharf\"")
                    && str_contains($user, 'Tomorrow they go to: Napa Valley')
                    && str_contains($user, 'the FIRST stop must be the transfer'))
                ->andReturn([
                    'weather_note' => 'Cold.', 'weather_tag' => 'outdoor', 'outfit_chips' => [], 'summary' => 'Granite walls.',
                    'hiccups' => ['Chains may be required.'],
                    'stops' => [
                        ['time' => '06:30', 'title' => 'Drive SF → Yosemite (~4 h)', 'description' => 'Leave early', 'option_label' => '', 'options' => []],
                        ['time' => '11:00', 'title' => 'Valley views', 'description' => '', 'option_label' => 'Where',
                            'options' => [['name' => 'Tunnel View', 'tier' => 'budget', 'note' => '', 'cost_min' => 0, 'cost_max' => 0, 'weather' => 'outdoor', 'map_query' => 'Tunnel View']]],
                    ],
                ]);
        });

        $this->actingAs($owner)->post(route('trips.days.generate', [$trip, $d2->id]))->assertRedirect();

        $stops = $d2->fresh()->stops()->orderBy('sort')->get();
        $this->assertSame('Drive SF → Yosemite (~4 h)', $stops[0]->title);
        $this->assertNull($stops[0]->thumb_url);                    // transfer rows get no picture
        $this->assertSame('pexels', $stops[1]->photo_source);         // no gallery caption for Tunnel View
        $this->actingAs($owner)->get(route('trips.show', $trip))->assertSee('Photo: Ana / Pexels');
    }
}
