<?php

namespace Tests\Feature;

use App\Models\Trip;
use App\Support\Affiliates;
use Database\Seeders\Seoul2026Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AffiliatesTest extends TestCase
{
    use RefreshDatabase;

    public function test_nothing_is_shown_until_a_url_is_configured(): void
    {
        $this->seed(Seoul2026Seeder::class);
        $trip = Trip::where('slug', 'seoul-2026')->firstOrFail();
        config(['affiliates.stays.url' => null, 'affiliates.things.url' => '', 'affiliates.esim.url' => null, 'affiliates.insurance.url' => null]);

        $this->assertSame([], Affiliates::forTrip($trip));
        $this->get(route('trips.show', $trip))->assertOk()->assertDontSee('Book your trip');
    }

    public function test_placeholders_are_url_encoded_and_non_https_templates_are_ignored(): void
    {
        $this->seed(Seoul2026Seeder::class);
        $trip = Trip::where('slug', 'seoul-2026')->firstOrFail();
        config([
            'affiliates.stays.url' => 'https://partner.example/s?q={destination}&in={checkin}&out={checkout}&n={adults}&ref=ABC',
            'affiliates.things.url' => 'http://insecure.example/?q={city}',
            'affiliates.esim.url' => null, 'affiliates.insurance.url' => null,
        ]);

        $links = Affiliates::forTrip($trip);
        $this->assertCount(1, $links);
        $this->assertStringContainsString('q=' . rawurlencode($trip->destination), $links[0]['url']);
        $this->assertStringContainsString('in=' . $trip->start_date->toDateString(), $links[0]['url']);

        $this->get(route('trips.show', $trip))->assertOk()->assertSee('Book your trip')->assertSee('rel="sponsored nofollow noopener noreferrer"', false)
            ->assertSee('may earn a small commission');
    }
}
