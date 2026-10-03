<?php

namespace Tests\Feature;

use App\Models\Stop;
use App\Models\StopOption;
use App\Models\Trip;
use App\Models\TripDay;
use App\Models\TripPick;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TripPicksTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Trip $trip;

    private Stop $stop;

    /** @var array<int, StopOption> */
    private array $options;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create(['name' => 'Lea']);
        [$this->trip, $this->stop, $this->options] = $this->makeTrip('osaka', $this->owner);
    }

    /** @return array{0: Trip, 1: Stop, 2: array<int, StopOption>} */
    private function makeTrip(string $slug, User $owner): array
    {
        $trip = Trip::create([
            'slug' => $slug, 'title' => ucfirst($slug), 'destination' => 'Osaka, Japan',
            'start_date' => '2027-04-01', 'end_date' => '2027-04-03', 'is_public' => false,
            'created_by' => $owner->id,
        ]);
        $trip->members()->attach($owner->id, ['role' => 'owner']);
        $day = TripDay::create(['trip_id' => $trip->id, 'day_number' => 1, 'sort' => 1, 'date' => '2027-04-01', 'title' => 'Day 1', 'hiccups' => []]);
        $stop = Stop::create(['trip_day_id' => $day->id, 'sort' => 1, 'time' => '12:00', 'title' => 'Lunch', 'has_options' => true]);
        $options = [
            StopOption::create(['stop_id' => $stop->id, 'sort' => 1, 'name' => 'Ramen bar', 'is_default_pick' => true]),
            StopOption::create(['stop_id' => $stop->id, 'sort' => 2, 'name' => 'Okonomiyaki']),
        ];

        return [$trip, $stop, $options];
    }

    private function member(string $role): User
    {
        $u = User::factory()->create();
        $this->trip->members()->attach($u->id, ['role' => $role]);

        return $u;
    }

    public function test_an_editor_can_pick_and_everyone_on_the_trip_sees_it(): void
    {
        $editor = $this->member('editor');
        $viewer = $this->member('viewer');

        $this->actingAs($editor)
            ->putJson(route('trips.picks.update', [$this->trip, $this->stop]), ['option_id' => $this->options[1]->id])
            ->assertOk()
            ->assertJson(['stopId' => $this->stop->id, 'optionId' => $this->options[1]->id, 'byId' => $editor->id]);

        $this->assertDatabaseHas('trip_picks', [
            'trip_id' => $this->trip->id, 'stop_id' => $this->stop->id,
            'stop_option_id' => $this->options[1]->id, 'picked_by' => $editor->id,
        ]);

        $this->actingAs($viewer)
            ->getJson(route('trips.picks.index', $this->trip))
            ->assertOk()
            ->assertJsonPath("picks.{$this->stop->id}.optionId", $this->options[1]->id);

        $this->actingAs($viewer)->get(route('trips.show', $this->trip))
            ->assertOk()
            ->assertSee('Picked by ' . $editor->name);
    }

    public function test_picking_again_replaces_the_groups_pick_rather_than_adding_one(): void
    {
        $this->actingAs($this->owner)->putJson(route('trips.picks.update', [$this->trip, $this->stop]), ['option_id' => $this->options[1]->id]);
        $this->actingAs($this->owner)->putJson(route('trips.picks.update', [$this->trip, $this->stop]), ['option_id' => $this->options[0]->id]);

        $this->assertSame(1, TripPick::count());
        $this->assertSame($this->options[0]->id, TripPick::first()->stop_option_id);
    }

    public function test_viewers_and_strangers_cannot_pick(): void
    {
        $viewer = $this->member('viewer');
        $stranger = User::factory()->create();
        $url = route('trips.picks.update', [$this->trip, $this->stop]);

        $this->actingAs($viewer)->putJson($url, ['option_id' => $this->options[1]->id])->assertForbidden();
        $this->actingAs($stranger)->putJson($url, ['option_id' => $this->options[1]->id])->assertNotFound();
        $this->assertSame(0, TripPick::count());
    }

    public function test_guests_cannot_pick_or_read_a_private_trips_picks(): void
    {
        $this->putJson(route('trips.picks.update', [$this->trip, $this->stop]), ['option_id' => $this->options[1]->id])
            ->assertUnauthorized();
        $this->getJson(route('trips.picks.index', $this->trip))->assertNotFound();
    }

    public function test_an_option_from_another_stop_is_rejected(): void
    {
        [, , $otherOptions] = $this->makeTrip('kyoto', $this->owner);

        $this->actingAs($this->owner)
            ->putJson(route('trips.picks.update', [$this->trip, $this->stop]), ['option_id' => $otherOptions[1]->id])
            ->assertStatus(422);
        $this->assertSame(0, TripPick::count());
    }

    public function test_a_stop_from_another_trip_is_not_found(): void
    {
        [, $otherStop, $otherOptions] = $this->makeTrip('kyoto', User::factory()->create());

        $this->actingAs($this->owner)
            ->putJson(route('trips.picks.update', [$this->trip, $otherStop]), ['option_id' => $otherOptions[1]->id])
            ->assertNotFound();
        $this->assertSame(0, TripPick::count());
    }

    public function test_clearing_a_pick_falls_back_to_the_default(): void
    {
        $this->actingAs($this->owner)->putJson(route('trips.picks.update', [$this->trip, $this->stop]), ['option_id' => $this->options[1]->id]);

        $this->actingAs($this->owner)
            ->deleteJson(route('trips.picks.destroy', [$this->trip, $this->stop]))
            ->assertOk()
            ->assertJson(['optionId' => null]);
        $this->assertSame(0, TripPick::count());
    }

    public function test_sample_trips_keep_per_device_picks_even_for_admins(): void
    {
        $sample = Trip::create([
            'slug' => 'sample', 'title' => 'Sample', 'destination' => 'Seoul',
            'start_date' => '2027-04-01', 'end_date' => '2027-04-03', 'is_public' => true, 'created_by' => null,
        ]);
        $this->assertFalse($sample->canPick(User::factory()->create()));
    }

    public function test_the_itinerary_marks_each_options_weather_fit_and_cost_for_swaps(): void
    {
        $this->options[1]->update(['name' => 'Kaiyukan Aquarium', 'cost_min' => 20, 'cost_max' => 30]);

        $this->actingAs($this->owner)->get(route('trips.show', $this->trip))
            ->assertOk()
            ->assertSee('data-weather="indoor"', false)
            ->assertSee('data-cost="25"', false)
            ->assertSee('class="wx-alert"', false)
            ->assertSee('id="pickDelta"', false);
    }
}
