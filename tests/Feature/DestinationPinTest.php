<?php

namespace Tests\Feature;

use App\Services\PlacesService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DestinationPinTest extends TestCase
{
    public function test_typed_destination_is_placed_with_coordinates(): void
    {
        config(['services.places.osm' => true]);
        Http::fake(['*' => Http::response(['features' => [['geometry' => ['coordinates' => [100.4935, 13.7525]]]]])]);

        $pin = app(PlacesService::class)->geocodeDestination('Bangkok, Thailand');

        $this->assertSame(['lat' => 13.7525, 'lon' => 100.4935], $pin);
    }

    public function test_unplaceable_destination_returns_null(): void
    {
        config(['services.places.osm' => true]);
        Http::fake(['*' => Http::response(['features' => []])]);

        $this->assertNull(app(PlacesService::class)->geocodeDestination('Nowhereville xyz'));
    }
}
