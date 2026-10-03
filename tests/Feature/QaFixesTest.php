<?php

namespace Tests\Feature;

use App\Models\Trip;
use App\Models\TripDay;
use App\Models\TripInvite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class QaFixesTest extends TestCase
{
    use RefreshDatabase;

    private function trip(User $owner, string $slug = 'paris'): Trip
    {
        $t = Trip::create(['slug' => $slug, 'title' => 'Paris weekend', 'destination' => 'Paris, France',
            'start_date' => '2027-05-01', 'end_date' => '2027-05-02', 'is_public' => false, 'created_by' => $owner->id]);
        $t->members()->attach($owner->id, ['role' => 'owner']);
        TripDay::create(['trip_id' => $t->id, 'day_number' => 1, 'sort' => 1, 'date' => '2027-05-01', 'title' => 'Day 1', 'hiccups' => []]);

        return $t;
    }

    public function test_ai_buttons_show_with_only_a_claude_key(): void
    {
        config(['services.gemini.api_key' => null, 'services.anthropic.api_key' => 'sk-ant-test']);
        $owner = User::factory()->create();
        $trip = $this->trip($owner);

        $this->actingAs($owner)->get(route('trips.show', $trip))
            ->assertOk()->assertSee('Draft this day with AI')->assertSee('tInput', false); // Trippie widget
    }

    public function test_a_very_long_duplicate_title_is_rejected_not_a_500(): void
    {
        $user = User::factory()->create();
        $trip = $this->trip(User::factory()->create());
        $trip->update(['is_public' => true]);

        $this->actingAs($user)->post(route('trips.duplicate', $trip), ['title' => Str::repeat('x', 300)])
            ->assertSessionHasErrors('title');
    }

    public function test_a_removed_member_cannot_rejoin_with_an_old_invite_but_can_with_a_new_one(): void
    {
        $owner = User::factory()->create();
        $friend = User::factory()->create();
        $trip = $this->trip($owner);
        $old = TripInvite::create(['trip_id' => $trip->id, 'token' => Str::random(40), 'role' => 'editor', 'created_by' => $owner->id]);

        $this->actingAs($friend)->post(route('trips.join.accept', $old->token))->assertRedirect();
        $this->actingAs($owner)->delete(route('trips.members.destroy', [$trip, $friend]))->assertRedirect();

        $this->actingAs($friend)->post(route('trips.join.accept', $old->token))->assertForbidden();
        $this->assertFalse($trip->fresh()->isMember($friend));

        $this->travel(1)->seconds();
        $new = TripInvite::create(['trip_id' => $trip->id, 'token' => Str::random(40), 'role' => 'viewer', 'created_by' => $owner->id]);
        $this->actingAs($friend)->post(route('trips.join.accept', $new->token))->assertRedirect();
        $this->assertTrue($trip->fresh()->isMember($friend));
    }

    public function test_owner_can_delete_a_trip_only_by_typing_its_name(): void
    {
        $owner = User::factory()->create();
        $trip = $this->trip($owner);

        $this->actingAs($owner)->delete(route('trips.destroy', $trip), ['confirm_title' => 'nope'])
            ->assertSessionHasErrors('confirm_title');
        $this->assertNotNull($trip->fresh());

        $this->actingAs($owner)->delete(route('trips.destroy', $trip), ['confirm_title' => 'Paris weekend'])
            ->assertRedirect(route('dashboard'));
        $this->assertNull($trip->fresh());
        $this->assertDatabaseMissing('trip_days', ['trip_id' => $trip->id]);
    }

    public function test_editors_cannot_delete_a_trip(): void
    {
        $owner = User::factory()->create();
        $editor = User::factory()->create();
        $trip = $this->trip($owner);
        $trip->members()->attach($editor->id, ['role' => 'editor']);

        $this->actingAs($editor)->delete(route('trips.destroy', $trip), ['confirm_title' => 'Paris weekend'])->assertForbidden();
        $this->assertNotNull($trip->fresh());
    }
}
