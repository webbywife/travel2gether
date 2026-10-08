<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\Seoul2026Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_regular_user_is_forbidden(): void
    {
        $this->seed(Seoul2026Seeder::class);
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('analytics'))->assertForbidden();
    }

    public function test_a_guest_is_redirected_to_login(): void
    {
        $this->get(route('analytics'))->assertRedirect(route('login'));
    }

    public function test_the_admin_can_view_analytics(): void
    {
        config(['app.admin_emails' => ['admin@example.com']]);
        $admin = User::factory()->create(['email' => 'admin@example.com']);

        $this->actingAs($admin)
            ->get(route('analytics'))
            ->assertOk()
            ->assertSee('Analytics', false)
            ->assertSee('Top airlines', false);
    }

    public function test_a_resolved_airline_and_route_show_up_in_the_totals(): void
    {
        config(['app.admin_emails' => ['admin@example.com']]);
        $admin = User::factory()->create(['email' => 'admin@example.com']);
        $owner = User::factory()->create();

        $this->actingAs($owner)->post(route('trips.store'), [
            'destination' => 'Kyoto, Japan',
            'arrival_date' => '2027-04-01',
            'departure_date' => '2027-04-05',
            'segments' => [
                ['from' => 'MNL', 'to' => 'KIX', 'airline' => 'Philippine Airlines', 'flight_no' => 'PR 408'],
                ['from' => 'KIX', 'to' => 'MNL', 'airline' => 'Philippine Airlines', 'flight_no' => 'PR 409'],
            ],
            'areas' => [['name' => 'Higashiyama', 'lat' => 34.9948, 'lon' => 135.7850]],
        ]);

        $this->actingAs($admin)
            ->get(route('analytics'))
            ->assertOk()
            ->assertSee('Philippine Airlines', false)
            ->assertSee('MNL', false)
            ->assertSee('KIX', false);
    }

    public function test_group_use_counts_members_trips_but_not_the_admins_or_samples(): void
    {
        config(['app.admin_emails' => ['admin@example.com']]);
        $admin = User::factory()->create(['email' => 'admin@example.com']);
        $owner = User::factory()->create();
        $payload = ['destination' => 'Kyoto, Japan', 'arrival_date' => '2027-04-01', 'departure_date' => '2027-04-03',
            'segments' => [['from' => 'MNL', 'to' => 'KIX'], ['from' => 'KIX', 'to' => 'MNL']], 'areas' => [['name' => 'Gion']]];
        $mine = app(\App\Actions\CreateTrip::class)($payload, $owner);
        $adminTrip = app(\App\Actions\CreateTrip::class)($payload, $admin);

        foreach ([$mine, $adminTrip] as $trip) {
            \App\Models\TripInvite::create(['trip_id' => $trip->id, 'created_by' => $trip->created_by]);
        }
        foreach (User::factory()->count(2)->create() as $friend) {
            $mine->members()->attach($friend->id, ['role' => 'editor']);
        }

        $this->post(route('trips.seen-hiccups', $mine))->assertNoContent();
        $this->assertSame(1, \Illuminate\Support\Facades\DB::table('visit_events')->where('event', 'hiccups')->count());

        $this->actingAs($admin)->get(route('analytics'))->assertOk()
            ->assertSee('Do groups use it?')
            ->assertViewHas('group', fn ($g) => $g['trips'] === 1 && $g['invites'] === 1 && $g['joined'] === 2 && $g['groupTrips'] === 1 && $g['hiccups'] === 1);
    }
}
