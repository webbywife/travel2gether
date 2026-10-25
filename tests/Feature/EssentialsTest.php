<?php

namespace Tests\Feature;

use App\Models\Trip;
use App\Models\User;
use Database\Seeders\Seoul2026Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class EssentialsTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_nearest_hospital_police_pharmacy_grocery_and_caches(): void
    {
        $this->seed(Seoul2026Seeder::class);
        $trip = Trip::where('slug', 'seoul-2026')->firstOrFail();
        $day = $trip->days()->first();
        $day->update(['hotel_lat' => 37.5665, 'hotel_lon' => 126.9780]);
        Cache::flush();
        config(['services.places.osm' => true]);

        Http::fake(['*' => Http::response(['elements' => [
            ['type' => 'node', 'lat' => 37.57, 'lon' => 126.98, 'tags' => ['amenity' => 'hospital', 'name' => 'City Hospital']],
            ['type' => 'node', 'lat' => 37.567, 'lon' => 126.979, 'tags' => ['amenity' => 'clinic', 'name' => 'Corner Clinic']],
            ['type' => 'node', 'lat' => 37.568, 'lon' => 126.978, 'tags' => ['amenity' => 'police', 'name' => 'Jung Police']],
            ['type' => 'node', 'lat' => 37.566, 'lon' => 126.977, 'tags' => ['amenity' => 'pharmacy', 'name' => 'Green Pharmacy']],
            ['type' => 'way', 'center' => ['lat' => 37.565, 'lon' => 126.976], 'tags' => ['shop' => 'supermarket', 'name' => 'Mart']],
            ['type' => 'node', 'lat' => 37.566, 'lon' => 126.977, 'tags' => ['amenity' => 'pharmacy']], // unnamed: skipped
        ]])]);

        $url = route('trips.days.essentials', [$trip, $day->id]);
        $json = $this->getJson($url)->assertOk()->json();

        $this->assertSame('City Hospital', $json['groups']['hospital'][0]['name']); // hospital before the closer clinic
        $this->assertSame('Corner Clinic', $json['groups']['hospital'][1]['name']);
        $this->assertSame(['Jung Police'], array_column($json['groups']['police'], 'name'));
        $this->assertSame(['Green Pharmacy'], array_column($json['groups']['pharmacy'], 'name'));
        $this->assertSame(['Mart'], array_column($json['groups']['grocery'], 'name'));
        $this->assertStringStartsWith('https://www.google.com/maps/search/', $json['groups']['police'][0]['map_url']);

        $this->getJson($url)->assertOk();
        Http::assertSentCount(1); // second call served from cache
    }

    public function test_private_trips_are_not_exposed_and_overpass_outage_is_graceful(): void
    {
        $owner = User::factory()->create();
        $trip = Trip::create(['slug' => 'mine', 'title' => 'Mine', 'destination' => 'Kyoto, Japan', 'start_date' => now(), 'end_date' => now()->addDay(),
            'is_public' => false, 'created_by' => $owner->id, 'lat' => 35.0, 'lon' => 135.7]);
        $trip->members()->attach($owner->id, ['role' => 'owner']);
        config(['services.places.osm' => true]);
        $day = $trip->days()->create(['day_number' => 1, 'date' => now(), 'title' => 'D1', 'hiccups' => [], 'sort' => 0]);

        $this->getJson(route('trips.days.essentials', [$trip, $day->id]))->assertNotFound();

        Cache::flush();
        Http::fake(['*' => Http::response('down', 504)]);
        $this->actingAs($owner)->getJson(route('trips.days.essentials', [$trip, $day->id]))->assertOk()->assertJson(['partial' => true, 'groups' => ['pharmacy' => null, 'grocery' => null]]);
    }

    public function test_photon_backup_gives_nearby_hospitals_and_police_only_and_drops_far_namesakes(): void
    {
        $this->seed(Seoul2026Seeder::class);
        $trip = Trip::where('slug', 'seoul-2026')->firstOrFail();
        $day = $trip->days()->first();
        $day->update(['hotel_lat' => 37.5665, 'hotel_lon' => 126.9780]);
        Cache::flush();
        config(['services.places.osm' => true]);

        Http::fake([
            'photon.komoot.io/*' => fn ($req) => Http::response(['features' => str_contains($req->url(), 'police')
                ? [['properties' => ['name' => 'Jung Police Station'], 'geometry' => ['coordinates' => [126.98, 37.57]]]]
                : [['properties' => ['name' => 'Near Hospital'], 'geometry' => ['coordinates' => [126.99, 37.58]]],
                   ['properties' => ['name' => 'Far Hospital'], 'geometry' => ['coordinates' => [100.5, 13.7]]]]]),
            '*' => Http::response('down', 504),
        ]);

        $json = $this->getJson(route('trips.days.essentials', [$trip, $day->id]))->assertOk()->json();

        $this->assertTrue($json['partial']);
        $this->assertSame(['Near Hospital'], array_column($json['groups']['hospital'], 'name'));
        $this->assertSame(['Jung Police Station'], array_column($json['groups']['police'], 'name'));
        $this->assertNull($json['groups']['pharmacy']);
        $this->assertNull($json['groups']['grocery']);
    }

    public function test_geoapify_answers_all_four_when_overpass_is_down_and_a_key_is_set(): void
    {
        $this->seed(Seoul2026Seeder::class);
        $trip = Trip::where('slug', 'seoul-2026')->firstOrFail();
        $day = $trip->days()->first();
        $day->update(['hotel_lat' => 37.5665, 'hotel_lon' => 126.9780]);
        Cache::flush();
        config(['services.places.osm' => true, 'services.geoapify.key' => 'test-key']);

        Http::fake([
            'api.geoapify.com/*' => fn ($req) => Http::response(['features' => [[
                'properties' => ['name' => 'Geo ' . (preg_match('/categories=([a-z.]+)/', urldecode($req->url()), $m) ? $m[1] : '?')],
                'geometry' => ['coordinates' => [126.98, 37.57]],
            ]]]),
            '*' => Http::response('down', 504),
        ]);

        $json = $this->getJson(route('trips.days.essentials', [$trip, $day->id]))->assertOk()->json();
        $this->assertFalse($json['partial']);
        $this->assertSame(['Geo healthcare.pharmacy'], array_column($json['groups']['pharmacy'], 'name'));
        $this->assertSame(['Geo commercial.supermarket'], array_column($json['groups']['grocery'], 'name'));
    }

    public function test_a_stale_good_answer_is_served_when_everything_is_down(): void
    {
        $this->seed(Seoul2026Seeder::class);
        $trip = Trip::where('slug', 'seoul-2026')->firstOrFail();
        $day = $trip->days()->first();
        $day->update(['hotel_lat' => 37.5665, 'hotel_lon' => 126.9780]);
        Cache::flush();
        config(['services.places.osm' => true]);
        $good = ['hospital' => [], 'police' => [], 'pharmacy' => [['name' => 'Old Pharmacy', 'km' => 0.4, 'lat' => 37.5, 'lon' => 127.0, 'map_url' => 'x']], 'grocery' => []];
        Cache::put('places:osm:essentials:37.567:126.978:last', $good, now()->addDays(30));
        Http::fake(['*' => Http::response('down', 504)]);

        $json = $this->getJson(route('trips.days.essentials', [$trip, $day->id]))->assertOk()->json();
        $this->assertFalse($json['partial']);
        $this->assertSame(['Old Pharmacy'], array_column($json['groups']['pharmacy'], 'name'));
    }
}
