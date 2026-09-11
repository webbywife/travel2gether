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
}
