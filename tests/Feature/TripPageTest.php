<?php

namespace Tests\Feature;

use App\Models\Trip;
use Database\Seeders\Seoul2026Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TripPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_renders_and_links_to_signup_and_the_sample(): void
    {
        $this->seed(Seoul2026Seeder::class);

        $this->get('/')
            ->assertOk()
            ->assertSee('Plan the trip', false)
            ->assertSee(route('register'), false)
            ->assertSee(route('trips.show', 'seoul-2026'), false);
    }

    public function test_seoul_trip_renders_entirely_from_the_database(): void
    {
        $this->seed(Seoul2026Seeder::class);

        $this->get('/t/seoul-2026')
            ->assertOk()
            ->assertSee('Seoul 2026', false)
            ->assertSee('Touchdown &amp; Sejong University', false)
            ->assertSee('Four Stones Coffee Roasters', false)
            ->assertSee('Possible hiccups', false)
            ->assertSee('Budget worksheet', false)
            ->assertSee('PR400', false)
            ->assertSee('Sample itinerary', false); // guest ribbon
    }

    public function test_private_trips_are_not_reachable(): void
    {
        $trip = Trip::create([
            'slug' => 'draft-trip',
            'title' => 'Draft Trip',
            'destination' => 'Nowhere',
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-03',
            'is_public' => false,
        ]);

        $this->get("/t/{$trip->slug}")->assertNotFound();
    }

    public function test_seeder_is_idempotent(): void
    {
        $this->seed(Seoul2026Seeder::class);
        $this->seed(Seoul2026Seeder::class);

        $this->assertSame(1, Trip::where('slug', 'seoul-2026')->count());
        $this->assertSame(5, Trip::first()->days()->count());
    }
}
