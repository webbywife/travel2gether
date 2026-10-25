<?php

namespace Tests\Feature;

use App\Models\Trip;
use App\Models\User;
use Database\Seeders\Seoul2026Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UseSampleTest extends TestCase
{
    use RefreshDatabase;

    private function sample(): Trip
    {
        $this->seed(Seoul2026Seeder::class);

        return Trip::where('slug', 'seoul-2026')->firstOrFail();
    }

    public function test_guest_copy_is_held_then_claimed_on_register_on_their_dates(): void
    {
        $sample = $this->sample();
        $arrival = now()->addMonths(2)->toDateString();

        $this->post(route('trips.use', $sample), ['arrival_date' => $arrival])->assertRedirect(route('register'));
        $this->assertSame(0, Trip::whereNotNull('created_by')->count());

        $this->post('/register', ['name' => 'Ana', 'email' => 'ana@example.com', 'password' => 'Str0ng-Pass-123!', 'password_confirmation' => 'Str0ng-Pass-123!']);

        $copy = Trip::whereNotNull('created_by')->firstOrFail();
        $this->assertSame($arrival, $copy->start_date->toDateString());
        $this->assertSame($arrival, $copy->days()->orderBy('day_number')->first()->date->toDateString());
        $this->assertSame($sample->days()->count(), $copy->days()->count());
        $this->assertEmpty($copy->segments);
        $this->assertSame($sample->start_date->diffInDays($sample->end_date), $copy->start_date->diffInDays($copy->end_date));
    }

    public function test_signed_in_user_copies_immediately(): void
    {
        $sample = $this->sample();
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('trips.use', $sample), ['arrival_date' => now()->addMonth()->toDateString()])
            ->assertRedirect();
        $this->assertSame(1, Trip::where('created_by', $user->id)->count());
    }

    public function test_past_date_and_non_samples_are_rejected(): void
    {
        $sample = $this->sample();
        $this->post(route('trips.use', $sample), ['arrival_date' => now()->subDay()->toDateString()])->assertSessionHasErrors('arrival_date');

        $private = Trip::create(['slug' => 'mine', 'title' => 'Mine', 'destination' => 'Kyoto, Japan', 'start_date' => now(), 'end_date' => now()->addDays(2), 'is_public' => false, 'created_by' => User::factory()->create()->id]);
        $this->post(route('trips.use', $private), ['arrival_date' => now()->addDay()->toDateString()])->assertNotFound();
    }

    public function test_guided_planner_renders_prefilled_and_posts_to_the_same_store(): void
    {
        $this->get(route('trips.start', ['destination' => 'Kyoto, Japan']))
            ->assertOk()->assertSee('Where are you going?', false)->assertSee('value="Kyoto, Japan"', false)
            ->assertSee(route('trips.store'), false);

        $this->post(route('trips.store'), [
            'destination' => 'Kyoto, Japan', 'arrival_date' => '2027-04-01', 'departure_date' => '2027-04-05',
            'party_size' => 2, 'currency' => 'PHP', 'segments' => [['from' => ''], ['from' => '']],
            'interests' => ['Food & drink', 'Balanced pace', 'Mid-range'],
        ])->assertRedirect(route('register'));
    }

    public function test_guided_starting_point_lands_on_the_flight_legs_and_blank_adds_none(): void
    {
        $base = ['destination' => 'Kyoto, Japan', 'arrival_date' => '2027-04-01', 'departure_date' => '2027-04-05', 'party_size' => 2, 'currency' => 'PHP'];
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('trips.store'), $base + ['segments' => [['from' => 'MNL'], ['to' => 'MNL']]]);
        $this->assertSame('MNL', Trip::where('created_by', $user->id)->latest('id')->first()->segments[0]['from']);

        $this->actingAs($user)->post(route('trips.store'), $base + ['destination' => 'Osaka, Japan', 'segments' => [['from' => ''], ['to' => '']]]);
        $this->assertEmpty(Trip::where('created_by', $user->id)->latest('id')->first()->segments);
    }

    public function test_guided_land_and_flight_home_times_anchor_first_and_last_day(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('trips.store'), [
            'destination' => 'Kyoto, Japan', 'arrival_date' => '2027-04-01', 'departure_date' => '2027-04-04', 'party_size' => 2, 'currency' => 'PHP',
            'segments' => [['from' => 'MNL', 'arrive' => '14:30'], ['to' => 'MNL', 'depart' => '21:15']],
        ]);
        $days = Trip::where('created_by', $user->id)->firstOrFail()->days()->with('stops')->orderBy('day_number')->get();

        $this->assertSame('14:30', $days->first()->stops->sortBy('sort')->first()->time);
        $this->assertContains('21:15', $days->last()->stops->pluck('time')->all());
    }
}
