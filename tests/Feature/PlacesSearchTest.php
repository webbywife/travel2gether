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

    public function test_search_503_when_no_key_and_free_search_is_switched_off(): void
    {
        config(['services.google.maps_key' => null, 'services.places.osm' => false]);

        $this->actingAs(User::factory()->create())
            ->getJson('/places/search?q=ramen')
            ->assertStatus(503);
    }

    public function test_without_a_google_key_search_uses_free_openstreetmap_photon(): void
    {
        config(['services.google.maps_key' => null, 'services.places.osm' => true]);
        Http::fake([
            'photon.komoot.io/*' => Http::response(['features' => [[
                'geometry' => ['coordinates' => [139.7005, 35.6595]],
                'properties' => [
                    'osm_type' => 'N', 'osm_id' => 123, 'name' => 'Shibuya Scramble Crossing',
                    'street' => 'Center-gai', 'city' => 'Tokyo', 'country' => 'Japan',
                    'osm_key' => 'tourism', 'osm_value' => 'attraction',
                ],
            ]]]),
        ]);

        $this->actingAs(User::factory()->create())
            ->getJson('/places/search?q=shibuya crossing&lat=35.66&lon=139.70')
            ->assertOk()
            ->assertJsonPath('results.0.name', 'Shibuya Scramble Crossing')
            ->assertJsonPath('results.0.provider', 'osm')
            ->assertJsonPath('results.0.provider_id', 'N123')
            ->assertJsonPath('results.0.lat', 35.6595)
            ->assertJsonPath('results.0.formatted_address', 'Center-gai, Tokyo, Japan');

        Http::assertNotSent(fn ($r) => str_contains($r->url(), 'googleapis.com'));
        Http::assertSent(fn ($r) => str_contains($r->url(), 'photon.komoot.io') && $r->hasHeader('User-Agent'));
    }

    public function test_nearby_uses_overpass_and_falls_back_to_a_mirror_when_the_main_server_is_busy(): void
    {
        config(['services.google.maps_key' => null, 'services.places.osm' => true]);
        Http::fake([
            'overpass-api.de/*' => Http::response('busy', 504),
            'overpass.private.coffee/*' => Http::response(['elements' => [
                ['type' => 'way', 'id' => 1, 'center' => ['lat' => 35.71, 'lon' => 139.79], 'tags' => ['name' => '浅草寺', 'name:en' => 'Sensō-ji', 'amenity' => 'place_of_worship', 'wikidata' => 'Q1']],
                ['type' => 'node', 'id' => 2, 'lat' => 35.71, 'lon' => 139.79, 'tags' => ['name' => 'Dozeu', 'amenity' => 'restaurant']],
                ['type' => 'node', 'id' => 3, 'lat' => 35.71, 'lon' => 139.79, 'tags' => ['name' => 'Ueno Park', 'leisure' => 'park']],
            ]]),
        ]);

        $rows = app(\App\Services\PlacesService::class)->nearby('Asakusa', 35.7148, 139.7967);

        $this->assertSame(['Sensō-ji', 'Dozeu', 'Ueno Park'], array_column($rows, 'name'));
        $this->assertSame('W1', $rows[0]['provider_id']);
    }

    public function test_when_every_overpass_server_fails_drafts_skip_grounding_for_a_while(): void
    {
        config(['services.google.maps_key' => null, 'services.places.osm' => true]);
        Http::fake(['*' => Http::response('busy', 504)]);
        $places = app(\App\Services\PlacesService::class);

        $this->assertSame([], $places->nearby('Asakusa', 35.7148, 139.7967));
        Http::assertSentCount(3); // tried each mirror once

        $this->assertSame([], $places->nearby('Ueno', 35.71, 139.77)); // paused — no new calls
        Http::assertSentCount(3);
    }
}
