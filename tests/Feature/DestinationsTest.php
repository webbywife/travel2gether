<?php

namespace Tests\Feature;

use App\Actions\DuplicateTrip;
use App\Models\Trip;
use App\Models\User;
use Database\Seeders\Seoul2026Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DestinationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_browse_page_lists_countries_and_links_cities_into_the_wizard(): void
    {
        $this->get('/destinations')
            ->assertOk()
            ->assertSee('Get inspired', false)
            ->assertSee('Kyoto', false)
            ->assertSee(route('trips.create', ['destination' => 'Kyoto, Japan']), false);
    }

    public function test_a_city_link_prefills_the_wizard_destination(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('trips.create', ['destination' => 'Bangkok, Thailand']))
            ->assertOk()
            ->assertSee('value="Bangkok, Thailand"', false);
    }

    public function test_the_trip_page_shows_a_visa_badge_for_a_matched_destination(): void
    {
        $this->seed(Seoul2026Seeder::class);

        $this->get('/t/seoul-2026')
            ->assertOk()
            ->assertSee('Visa required', false)
            ->assertSee('Tourist visa required.', false);
    }

    public function test_no_badge_for_an_unlisted_destination(): void
    {
        $this->seed(Seoul2026Seeder::class);
        $owner = User::factory()->create();
        $trip = app(DuplicateTrip::class)(Trip::where('slug', 'seoul-2026')->first(), $owner, 'A trip');
        $trip->update(['destination' => 'Nowhereland']);

        $this->actingAs($owner)
            ->get(route('trips.show', $trip))
            ->assertOk()
            ->assertDontSee('for a PH passport', false);
    }
}
