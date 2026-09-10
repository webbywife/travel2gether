<?php

namespace Tests\Feature;

use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TripEditTest extends TestCase
{
    use RefreshDatabase;

    private function trip(User $owner): Trip
    {
        $trip = Trip::create([
            'slug' => 'cal2026',
            'title' => 'CAL2026',
            'destination' => 'California Trip',
            'start_date' => '2026-10-31',
            'end_date' => '2026-11-07',
            'is_public' => false,
            'created_by' => $owner->id,
        ]);
        $trip->members()->attach($owner->id, ['role' => 'owner']);

        return $trip;
    }

    public function test_a_guest_cannot_edit(): void
    {
        $owner = User::factory()->create();
        $trip = $this->trip($owner);

        $this->get(route('trips.edit', $trip))->assertRedirect(route('login'));
        $this->patch(route('trips.update', $trip), ['destination' => 'Nowhere'])->assertRedirect(route('login'));
    }

    public function test_the_owner_can_update_the_destination_and_it_fixes_the_visa_badge(): void
    {
        $owner = User::factory()->create();
        $trip = $this->trip($owner);

        $this->actingAs($owner)
            ->patch(route('trips.update', $trip), [
                'destination' => 'San Francisco, USA',
                'title' => 'California Trip',
                'hotel_name' => 'Hotel Zephyr',
            ])
            ->assertRedirect(route('trips.show', $trip));

        $trip->refresh();
        $this->assertSame('San Francisco, USA', $trip->destination);
        $this->assertSame('Hotel Zephyr', $trip->hotel_name);

        $this->actingAs($owner)
            ->get(route('trips.show', $trip))
            ->assertOk()
            ->assertSee('Visa required', false)
            ->assertSee('B1/B2 visitor visa required', false);
    }

    public function test_an_editor_can_update_but_a_viewer_cannot(): void
    {
        $owner = User::factory()->create();
        $trip = $this->trip($owner);

        $editor = User::factory()->create();
        $trip->members()->attach($editor->id, ['role' => 'editor']);
        $this->actingAs($editor)
            ->patch(route('trips.update', $trip), ['destination' => 'Osaka, Japan'])
            ->assertRedirect(route('trips.show', $trip));
        $this->assertSame('Osaka, Japan', $trip->fresh()->destination);

        $viewer = User::factory()->create();
        $trip->members()->attach($viewer->id, ['role' => 'viewer']);
        $this->actingAs($viewer)
            ->patch(route('trips.update', $trip), ['destination' => 'Should not save'])
            ->assertForbidden();
        $this->assertSame('Osaka, Japan', $trip->fresh()->destination);
    }

    public function test_a_non_member_cannot_edit_someone_elses_trip(): void
    {
        $owner = User::factory()->create();
        $trip = $this->trip($owner);
        $stranger = User::factory()->create();

        $this->actingAs($stranger)->get(route('trips.edit', $trip))->assertForbidden();
        $this->actingAs($stranger)
            ->patch(route('trips.update', $trip), ['destination' => 'Nowhere'])
            ->assertForbidden();
    }

    public function test_blank_title_falls_back_to_a_default(): void
    {
        $owner = User::factory()->create(['name' => 'Marco']);
        $trip = $this->trip($owner);

        $this->actingAs($owner)->patch(route('trips.update', $trip), [
            'destination' => 'Bangkok, Thailand',
            'title' => '',
        ]);

        $this->assertSame("Marco's Bangkok, Thailand trip", $trip->fresh()->title);
    }
}
