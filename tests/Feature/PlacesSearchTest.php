<?php

namespace Tests\Feature;

use App\Models\Place;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PlacesSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.google.maps_key' => 'test-maps-key']);
    }

    public function test_search_requires_authentication(): void
    {
        $this->getJson('/places/search?q=ramen')->assertUnauthorized();
    }

    public function test_search_returns_normalized_results(): void
    {
        Http::fake([
            'places.googleapis.com/v1/places:searchText' => Http::response([
                'places' => [[
                    'id' => 'ChIJ_seoul_1',
                    'displayName' => ['text' => 'Sam\'s Korean BBQ'],
                    'formattedAddress' => 'Gangnam, Seoul',
                    'location' => ['latitude' => 37.5, 'longitude' => 127.05],
                    'types' => ['restaurant', 'food'],
                    'rating' => 4.6,
                    'userRatingCount' => 1200,
                    'priceLevel' => 'PRICE_LEVEL_MODERATE',
                ]],
            ]),
        ]);

        $this->actingAs(User::factory()->create())
            ->getJson('/places/search?q=korean bbq&lat=37.5&lon=127.05')
            ->assertOk()
            ->assertJsonPath('results.0.name', "Sam's Korean BBQ")
            ->assertJsonPath('results.0.provider_id', 'ChIJ_seoul_1')
            ->assertJsonPath('results.0.rating', 4.6);
    }

    public function test_search_is_cached_so_the_api_is_hit_once(): void
    {
        Http::fake([
            'places.googleapis.com/*' => Http::response(['places' => []]),
        ]);

        $user = User::factory()->create();
        $this->actingAs($user)->getJson('/places/search?q=coffee')->assertOk();
        $this->actingAs($user)->getJson('/places/search?q=coffee')->assertOk();

        Http::assertSentCount(1);
    }

    public function test_details_persist_into_the_places_table(): void
    {
        Http::fake([
            'places.googleapis.com/v1/places/ChIJ_x' => Http::response([
                'id' => 'ChIJ_x',
                'displayName' => ['text' => 'Namsan Tower'],
                'formattedAddress' => 'Yongsan-gu, Seoul',
                'location' => ['latitude' => 37.551, 'longitude' => 126.988],
                'types' => ['tourist_attraction'],
                'websiteUri' => 'https://example.com',
            ]),
        ]);

        $this->actingAs(User::factory()->create())
            ->getJson('/places/ChIJ_x')
            ->assertOk()
            ->assertJsonPath('name', 'Namsan Tower');

        $this->assertDatabaseHas('places', [
            'provider' => 'google',
            'provider_id' => 'ChIJ_x',
            'name' => 'Namsan Tower',
        ]);
        $this->assertNotNull(Place::first()->details_fetched_at);
    }

    public function test_search_503_when_no_key_configured(): void
    {
        config(['services.google.maps_key' => null]);

        $this->actingAs(User::factory()->create())
            ->getJson('/places/search?q=ramen')
            ->assertStatus(503);
    }
}
