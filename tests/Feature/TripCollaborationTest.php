<?php

namespace Tests\Feature;

use App\Models\Trip;
use App\Models\TripInvite;
use App\Models\User;
use Database\Seeders\Seoul2026Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TripCollaborationTest extends TestCase
{
    use RefreshDatabase;

    private function sample(): Trip
    {
        $this->seed(Seoul2026Seeder::class);

        return Trip::where('slug', 'seoul-2026')->first();
    }

    public function test_a_user_can_duplicate_the_sample_into_their_own_private_trip(): void
    {
        $sample = $this->sample();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('trips.duplicate', $sample))
            ->assertRedirect();

        $copy = Trip::where('created_by', $user->id)->first();
        $this->assertNotNull($copy);
        $this->assertFalse($copy->is_public);
        $this->assertSame('owner', $copy->roleFor($user));
        $this->assertSame(5, $copy->days()->count());
        $this->assertSame($sample->stops()->count(), $copy->stops()->count());
        $this->assertSame($sample->budgetLines()->count(), $copy->budgetLines()->count());
    }

    public function test_private_trips_are_404_for_non_members_but_visible_to_members(): void
    {
        $owner = User::factory()->create();
        $trip = Trip::create([
            'slug' => 'paris', 'title' => 'Paris', 'destination' => 'Paris, France',
            'start_date' => '2027-05-01', 'end_date' => '2027-05-05', 'is_public' => false,
            'created_by' => $owner->id,
        ]);
        $trip->members()->attach($owner->id, ['role' => 'owner']);

        $stranger = User::factory()->create();
        $viewer = User::factory()->create();
        $trip->members()->attach($viewer->id, ['role' => 'viewer']);

        $this->get(route('trips.show', $trip))->assertNotFound();               // guest
        $this->actingAs($stranger)->get(route('trips.show', $trip))->assertNotFound();
        $this->actingAs($viewer)->get(route('trips.show', $trip))->assertOk();
        $this->actingAs($owner)->get(route('trips.show', $trip))->assertOk();
    }

    public function test_only_the_owner_can_create_invite_links(): void
    {
        $sample = $this->sample();
        $owner = User::factory()->create();
        $copy = app(\App\Actions\DuplicateTrip::class)($sample, $owner);

        $editor = User::factory()->create();
        $copy->members()->attach($editor->id, ['role' => 'editor']);

        $this->actingAs($editor)
            ->post(route('trips.invites.store', $copy), ['role' => 'editor'])
            ->assertForbidden();

        $this->actingAs($owner)
            ->post(route('trips.invites.store', $copy), ['role' => 'editor'])
            ->assertRedirect();

        $this->assertSame(1, $copy->invites()->count());
    }

    public function test_an_invite_link_adds_the_visitor_as_a_collaborator(): void
    {
        $sample = $this->sample();
        $owner = User::factory()->create();
        $copy = app(\App\Actions\DuplicateTrip::class)($sample, $owner);

        $invite = $copy->invites()->create(['role' => 'editor', 'created_by' => $owner->id]);

        $marco = User::factory()->create();
        $this->actingAs($marco)->get(route('trips.join', $invite->token))->assertOk();
        $this->actingAs($marco)->post(route('trips.join.accept', $invite->token))
            ->assertRedirect(route('trips.show', $copy));

        $this->assertSame('editor', $copy->fresh()->roleFor($marco));
        $this->assertSame(1, $invite->fresh()->uses);
    }

    public function test_a_revoked_invite_cannot_be_accepted(): void
    {
        $sample = $this->sample();
        $owner = User::factory()->create();
        $copy = app(\App\Actions\DuplicateTrip::class)($sample, $owner);
        $invite = $copy->invites()->create(['role' => 'viewer', 'created_by' => $owner->id, 'revoked_at' => now()]);

        $this->actingAs(User::factory()->create())
            ->post(route('trips.join.accept', $invite->token))
            ->assertStatus(410);
    }

    public function test_owner_can_remove_a_collaborator_but_not_themselves(): void
    {
        $sample = $this->sample();
        $owner = User::factory()->create();
        $copy = app(\App\Actions\DuplicateTrip::class)($sample, $owner);
        $marco = User::factory()->create();
        $copy->members()->attach($marco->id, ['role' => 'editor']);

        $this->actingAs($owner)->delete(route('trips.members.destroy', [$copy, $marco]))->assertRedirect();
        $this->assertFalse($copy->fresh()->isMember($marco));

        $this->actingAs($owner)->delete(route('trips.members.destroy', [$copy, $owner]))->assertForbidden();
        $this->assertTrue($copy->fresh()->isMember($owner));
    }

    public function test_dashboard_lists_owned_and_shared_trips(): void
    {
        $sample = $this->sample();
        $me = User::factory()->create();
        $friend = User::factory()->create();

        $mine = app(\App\Actions\DuplicateTrip::class)($sample, $me);
        $theirs = app(\App\Actions\DuplicateTrip::class)($sample, $friend);
        $theirs->members()->attach($me->id, ['role' => 'viewer']);

        $this->actingAs($me)->get('/dashboard')
            ->assertOk()
            ->assertSee($mine->title)
            ->assertSee($theirs->title)
            ->assertSee('Shared with you');
    }
}
