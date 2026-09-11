<?php

namespace Tests\Feature;

use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MembershipLimitsTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_replace_recursive([
            'destination' => 'Kyoto, Japan',
            'arrival_date' => '2027-04-01',
            'departure_date' => '2027-04-05',
            'segments' => [['from' => 'MNL', 'to' => 'KIX'], ['from' => 'KIX', 'to' => 'MNL']],
            'areas' => [['name' => 'Higashiyama', 'lat' => 34.9948, 'lon' => 135.7850]],
        ], $overrides);
    }

    public function test_a_free_member_is_blocked_after_the_trip_limit(): void
    {
        $user = User::factory()->create();

        for ($i = 0; $i < User::FREE_TRIP_LIMIT; $i++) {
            $this->actingAs($user)
                ->post(route('trips.store'), $this->payload(['title' => "Trip {$i}"]))
                ->assertRedirect();
        }
        $this->assertSame(User::FREE_TRIP_LIMIT, $user->ownedTrips()->count());

        $this->actingAs($user)
            ->get(route('trips.create'))
            ->assertRedirect(route('upgrade'));

        $this->actingAs($user)
            ->post(route('trips.store'), $this->payload(['title' => 'One too many']))
            ->assertRedirect(route('upgrade'));

        // the blocked attempt didn't create anything
        $this->assertSame(User::FREE_TRIP_LIMIT, $user->fresh()->ownedTrips()->count());
    }

    public function test_a_paid_member_has_no_trip_limit(): void
    {
        $user = User::factory()->create(['subscription' => 'paid']);

        for ($i = 0; $i < User::FREE_TRIP_LIMIT + 2; $i++) {
            $this->actingAs($user)->post(route('trips.store'), $this->payload(['title' => "Trip {$i}"]));
        }

        $this->assertSame(User::FREE_TRIP_LIMIT + 2, $user->ownedTrips()->count());
    }

    public function test_a_paid_trip_owner_gets_unlimited_regenerations_for_collaborators_too(): void
    {
        $owner = User::factory()->create(['subscription' => 'paid']);
        $trip = Trip::create([
            'slug' => 'paid-trip', 'title' => 'Paid Trip', 'destination' => 'Nowhere',
            'start_date' => '2027-01-01', 'end_date' => '2027-01-03',
            'is_public' => false, 'created_by' => $owner->id, 'regenerations_used' => 5,
        ]);
        $editor = User::factory()->create();
        $trip->members()->attach($editor->id, ['role' => 'editor']);

        $this->assertTrue($trip->canRegenerate($editor));
        $this->assertTrue($trip->canRegenerate($owner));
    }

    public function test_a_free_trip_owner_is_capped_regardless_of_who_clicks(): void
    {
        $owner = User::factory()->create();
        $trip = Trip::create([
            'slug' => 'free-trip', 'title' => 'Free Trip', 'destination' => 'Nowhere',
            'start_date' => '2027-01-01', 'end_date' => '2027-01-03',
            'is_public' => false, 'created_by' => $owner->id, 'regenerations_used' => 1,
        ]);

        $this->assertFalse($trip->canRegenerate($owner));
    }

    public function test_only_an_admin_can_toggle_a_members_subscription(): void
    {
        config(['app.admin_emails' => ['admin@example.com']]);
        $admin = User::factory()->create(['email' => 'admin@example.com']);
        $member = User::factory()->create(['subscription' => 'free']);
        $stranger = User::factory()->create();

        $this->actingAs($stranger)
            ->patch(route('analytics.toggle-subscription', $member))
            ->assertForbidden();

        $this->actingAs($admin)
            ->patch(route('analytics.toggle-subscription', $member))
            ->assertRedirect();

        $this->assertSame('paid', $member->fresh()->subscription);

        $this->actingAs($admin)->patch(route('analytics.toggle-subscription', $member));
        $this->assertSame('free', $member->fresh()->subscription);
    }
}
