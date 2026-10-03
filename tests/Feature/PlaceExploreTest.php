<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\PlacesService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PlaceExploreTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.google.maps_key' => null, 'services.places.osm' => true]);
    }

    private function feature(string $name, float $lat, float $lon, string $key, string $value, string $city = 'Tokyo'): array
    {
        return ['geometry' => ['coordinates' => [$lon, $lat]], 'properties' => [
            'osm_type' => 'N', 'osm_id' => crc32($name . $lat), 'name' => $name, 'osm_key' => $key, 'osm_value' => $value,
            'city' => $city, 'country' => 'Japan']];
    }

    public function test_hotel_search_sends_hotel_filters_labels_results_and_drops_far_namesakes(): void
    {
        Http::fake(['photon.komoot.io/*' => Http::response(['features' => [
            $this->feature('ibis Styles Tokyo Ginza', 35.67, 139.76, 'tourism', 'hotel'),
            $this->feature('Ibis', -26.9, -49.07, 'tourism', 'hotel', 'Blumenau'), // Brazil
        ]])]);

        $rows = app(PlacesService::class)->search('ibis', 35.69, 139.70, 8, 'hotel');

        $this->assertSame(['ibis Styles Tokyo Ginza'], array_column($rows, 'name'));
        $this->assertSame('Hotel', $rows[0]['category']);
        Http::assertSent(fn ($r) => str_contains(urldecode($r->url()), 'osm_tag=tourism:hotel'));
    }

    public function test_area_search_asks_for_districts_and_towns(): void
    {
        Http::fake(['photon.komoot.io/*' => Http::response(['features' => [$this->feature('Shinjuku', 35.69, 139.70, 'place', 'quarter')]])]);
        $rows = app(PlacesService::class)->search('shinjuku', 35.68, 139.76, 8, 'area');

        $this->assertSame('Neighbourhood', $rows[0]['category']);
        Http::assertSent(fn ($r) => str_contains($r->url(), 'layer=district') && str_contains($r->url(), 'layer=locality'));
    }

    public function test_nearest_airports_come_from_the_bundled_list(): void
    {
        $codes = array_column(PlacesService::nearestAirports(35.6938, 139.7034), 'provider_id');
        $this->assertSame('HND', $codes[0]);
        $this->assertContains('NRT', $codes);
    }

    public function test_explore_groups_hotels_and_landmarks_and_keeps_airports_when_photon_is_down(): void
    {
        Http::fake(['photon.komoot.io/*' => Http::response(['features' => [
            $this->feature('Hotel Gracery Shinjuku', 35.6946, 139.7021, 'tourism', 'hotel'),
            $this->feature('Shinjuku Gyoen National Garden', 35.6852, 139.7101, 'leisure', 'park'),
        ]])]);
        $e = app(PlacesService::class)->explore(35.6938, 139.7034);
        $this->assertSame('HND', $e['airports'][0]['provider_id']);
        $this->assertContains('Hotel Gracery Shinjuku', array_column($e['hotels'], 'name'));
        $this->assertContains('Shinjuku Gyoen National Garden', array_column($e['landmarks'], 'name'));

    }

    public function test_explore_still_lists_airports_when_photon_is_down(): void
    {
        Http::fake(['*' => Http::response('down', 503)]);
        $e = app(PlacesService::class)->explore(37.4979, 127.0276);
        $this->assertTrue($e['partial']);
        $this->assertSame('GMP', $e['airports'][0]['provider_id']);
    }

    public function test_explore_needs_a_signed_in_user_and_valid_coordinates(): void
    {
        $this->getJson('/places/explore?lat=35.69&lon=139.70')->assertUnauthorized();
        $this->actingAs(User::factory()->create())->getJson('/places/explore?lat=999&lon=0')->assertStatus(422);
    }
}
