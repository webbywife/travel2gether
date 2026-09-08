<?php

namespace Tests\Feature;

use App\Models\Place;
use App\Models\Trip;
use App\Models\User;
use Database\Seeders\Seoul2026Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TripPrintTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_printable_scrapbook_renders_for_the_public_sample(): void
    {
        $this->seed(Seoul2026Seeder::class);

        $this->get(route('trips.print', 'seoul-2026'))
            ->assertOk()
            ->assertSee('Seoul 2026', false)
            ->assertSee('Print / Save as PDF', false)
            ->assertSee('Budget worksheet', false);
    }

    public function test_a_recommended_place_appears_in_the_favorites_section(): void
    {
        $this->seed(Seoul2026Seeder::class);
        $trip = Trip::where('slug', 'seoul-2026')->first();
        $stop = $trip->days->first()->stops->first();

        $place = Place::create(['provider' => 'gemini', 'provider_id' => 'favorite-spot', 'name' => 'Everyone\'s Favorite Spot']);
        $option = $stop->options()->create(['place_id' => $place->id, 'sort' => 99, 'name' => $place->name]);
        $place->recommendations()->create(['user_id' => User::factory()->create()->id, 'vote' => 1]);

        $this->get(route('trips.print', $trip))
            ->assertOk()
            ->assertSee('Our favorites', false)
            ->assertSee('Everyone&#039;s Favorite Spot', false);
    }

    public function test_a_private_trip_cannot_be_printed_by_an_outsider(): void
    {
        $trip = Trip::create([
            'slug' => 'secret-trip',
            'title' => 'Secret Trip',
            'destination' => 'Nowhere',
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-03',
            'is_public' => false,
        ]);

        $this->get(route('trips.print', $trip))->assertNotFound();
    }
}
