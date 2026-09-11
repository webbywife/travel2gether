<?php

namespace Tests\Feature;

use App\Actions\DuplicateTrip;
use App\Models\Trip;
use App\Models\User;
use Database\Seeders\Seoul2026Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TripDayEditTest extends TestCase
{
    use RefreshDatabase;

    private function ownedTrip(User $owner): Trip
    {
        $this->seed(Seoul2026Seeder::class);

        return app(DuplicateTrip::class)(Trip::where('slug', 'seoul-2026')->first(), $owner);
    }

    public function test_a_guest_cannot_edit_a_day(): void
    {
        $owner = User::factory()->create();
        $trip = $this->ownedTrip($owner);
        $day = $trip->days()->first();

        $this->get(route('trips.days.edit', [$trip, $day]))->assertRedirect(route('login'));
        $this->patch(route('trips.days.update', [$trip, $day]), ['title' => 'Nope'])->assertRedirect(route('login'));
    }

    public function test_the_owner_can_edit_a_days_details(): void
    {
        $owner = User::factory()->create();
        $trip = $this->ownedTrip($owner);
        $day = $trip->days()->first();

        $this->actingAs($owner)
            ->get(route('trips.days.edit', [$trip, $day]))
            ->assertOk()
            ->assertSee($day->title);

        $this->actingAs($owner)
            ->patch(route('trips.days.update', [$trip, $day]), [
                'title' => 'A whole new title',
                'area_label' => 'Downtown',
                'hotel_name' => 'Backup Hotel',
                'summary' => 'Updated summary.',
            ])
            ->assertRedirect(route('trips.show', $trip) . '#' . $day->day_number);

        $day->refresh();
        $this->assertSame('A whole new title', $day->title);
        $this->assertSame('Downtown', $day->area_label);
        $this->assertSame('Backup Hotel', $day->hotel_name);
        $this->assertSame('Updated summary.', $day->summary);

        // stops are untouched by this edit
        $this->assertGreaterThan(0, $day->stops()->count());
    }

    public function test_a_viewer_cannot_edit_a_day(): void
    {
        $owner = User::factory()->create();
        $trip = $this->ownedTrip($owner);
        $viewer = User::factory()->create();
        $trip->members()->attach($viewer->id, ['role' => 'viewer']);
        $day = $trip->days()->first();

        $this->actingAs($viewer)
            ->patch(route('trips.days.update', [$trip, $day]), ['title' => 'Nope'])
            ->assertForbidden();

        $this->assertNotSame('Nope', $day->fresh()->title);
    }

    public function test_a_day_from_a_different_trip_404s(): void
    {
        $owner = User::factory()->create();
        $tripA = $this->ownedTrip($owner);
        $tripB = app(DuplicateTrip::class)(Trip::where('slug', 'seoul-2026')->first(), $owner, 'Second copy');

        $dayFromB = $tripB->days()->first();

        $this->actingAs($owner)
            ->get(route('trips.days.edit', [$tripA, $dayFromB]))
            ->assertNotFound();
    }
}
