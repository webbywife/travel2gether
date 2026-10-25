<?php

namespace Tests\Feature;

use App\Models\Place;
use App\Models\Trip;
use Database\Seeders\Seoul2026Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TripMapTest extends TestCase
{
    use RefreshDatabase;

    public function test_map_data_numbers_option_stops_and_flags_unlocated_ones(): void
    {
        $this->seed(Seoul2026Seeder::class);
        $trip = Trip::where('slug', 'seoul-2026')->firstOrFail();
        $json = $this->getJson(route('trips.map', $trip))->assertOk()->json();

        $this->assertCount($trip->days()->count(), $json['days']);
        $stops = $json['days'][0]['stops'];
        $this->assertSame(range(1, count($stops)), array_column($stops, 'n'));
        $this->assertNull($stops[0]['lat']);
        $this->assertStringContainsString('/locate', $stops[0]['locate_url']);

        // A stop whose option already has coordinates needs no lookup.
        $opt = $trip->days()->first()->stops()->whereHas('options')->first()->options()->first();
        $place = Place::create(['provider' => 'osm', 'provider_id' => 'x1', 'name' => $opt->name, 'lat' => 37.5, 'lon' => 127.0]);
        $opt->update(['place_id' => $place->id]);
        $first = $this->getJson(route('trips.map', $trip))->json('days.0.stops.0');
        $this->assertEquals(37.5, $first['lat']);
        $this->assertNull($first['locate_url']);
    }

    public function test_locate_finds_a_nearby_match_ignores_far_namesakes_and_caches(): void
    {
        $this->seed(Seoul2026Seeder::class);
        $trip = Trip::where('slug', 'seoul-2026')->firstOrFail();
        $stop = $trip->days()->first()->stops()->whereHas('options')->firstOrFail();
        Cache::flush();
        config(['services.places.osm' => true]);

        Http::fake(['photon.komoot.io/*' => Http::response(['features' => [
            ['geometry' => ['coordinates' => [100.5, 13.7]]],               // same name in Bangkok: ignored
            ['geometry' => ['coordinates' => [126.99, 37.57]]],             // in Seoul
        ]])]);

        $url = route('trips.stops.locate', [$trip, $stop->id]);
        $this->getJson($url)->assertOk()->assertJson(['found' => true, 'lat' => 37.57, 'lon' => 126.99]);
        $this->getJson($url)->assertOk();
        Http::assertSentCount(1);

        // A stop that belongs to another trip is not reachable through this one.
        $other = Trip::create(['slug' => 'o', 'title' => 'o', 'destination' => 'Kyoto', 'start_date' => now(), 'end_date' => now(), 'is_public' => true]);
        $this->getJson(route('trips.stops.locate', [$other, $stop->id]))->assertNotFound();
    }
}
