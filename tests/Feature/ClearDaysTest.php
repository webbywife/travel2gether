<?php

namespace Tests\Feature;

use App\Actions\CreateTrip;
use App\Models\TripPick;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClearDaysTest extends TestCase
{
    use RefreshDatabase;

    private function trip(User $owner)
    {
        $trip = app(CreateTrip::class)([
            'destination' => 'Kyoto, Japan', 'party_size' => 2, 'currency' => 'JPY',
            'arrival_date' => '2027-04-01', 'departure_date' => '2027-04-04',
            'segments' => [['from' => 'MNL', 'to' => 'KIX'], ['from' => 'KIX', 'to' => 'MNL']],
            'areas' => [['name' => 'Higashiyama', 'lat' => 34.99, 'lon' => 135.78], ['name' => 'Arashiyama', 'lat' => 35.01, 'lon' => 135.67]],
        ], $owner);

        // Day 2 gets a drafted stop with options and a pick, as if Trippie had filled it.
        $day = $trip->days()->where('day_number', 2)->first();
        $day->update(['source' => 'ai']);
        $stop = $day->stops()->create(['sort' => 9, 'time' => '10:00', 'title' => 'Kiyomizu-dera', 'has_options' => true]);
        $opt = $stop->options()->create(['sort' => 0, 'name' => 'Temple walk']);
        TripPick::create(['trip_id' => $trip->id, 'stop_id' => $stop->id, 'stop_option_id' => $opt->id, 'picked_by' => $owner->id]);

        return [$trip, $day];
    }

    public function test_the_owner_can_clear_a_day_back_to_blank_slots(): void
    {
        $owner = User::factory()->create();
        [$trip, $day] = $this->trip($owner);
        $this->actingAs($owner)->get(route('trips.show', $trip))->assertOk()->assertSee('Clear day')->assertSee('Reset itinerary');

        $this->actingAs($owner)->post(route('trips.days.clear', [$trip, $day->id]))->assertRedirect();

        $day->refresh();
        $this->assertSame(['Morning in Higashiyama', 'Lunch near Higashiyama', 'Afternoon in Higashiyama', 'Dinner'],
            $day->stops()->orderBy('sort')->pluck('title')->all());
        $this->assertSame(0, TripPick::where('trip_id', $trip->id)->count());
        $this->assertSame('ai', $day->source); // clearing doesn't hand back the free re-draft
        $this->assertSame('2027-04-02', $day->date->toDateString());
    }

    public function test_editors_cannot_clear_and_arrival_departure_days_are_kept(): void
    {
        $owner = User::factory()->create();
        [$trip, $day] = $this->trip($owner);
        $editor = User::factory()->create();
        $trip->members()->attach($editor->id, ['role' => 'editor']);

        $this->actingAs($editor)->get(route('trips.show', $trip))->assertOk()->assertDontSee('Reset itinerary');
        $this->actingAs($editor)->post(route('trips.days.clear', [$trip, $day->id]))->assertForbidden();
        $this->actingAs($editor)->post(route('trips.days.clear-all', $trip))->assertForbidden();
        $this->assertSame(1, TripPick::where('trip_id', $trip->id)->count());

        $arrival = $trip->days()->where('day_number', 1)->first();
        $before = $arrival->stops()->pluck('title')->all();
        $this->actingAs($owner)->post(route('trips.days.clear', [$trip, $arrival->id]))->assertSessionHas('error');
        $this->assertSame($before, $arrival->stops()->pluck('title')->all());
    }

    public function test_reset_clears_every_middle_day_only(): void
    {
        $owner = User::factory()->create();
        [$trip] = $this->trip($owner);
        $first = $trip->days()->where('day_number', 1)->first()->stops()->pluck('title')->all();

        $this->actingAs($owner)->post(route('trips.days.clear-all', $trip))->assertSessionHas('status');

        $this->assertSame(0, TripPick::where('trip_id', $trip->id)->count());
        $this->assertSame($first, $trip->days()->where('day_number', 1)->first()->stops()->pluck('title')->all());
        foreach ($trip->days()->whereIn('day_number', [2, 3])->get() as $d) {
            $this->assertSame(4, $d->stops()->count());
        }
    }

    public function test_the_planner_per_diem_is_worked_out_per_meal_not_typed_as_a_total(): void
    {
        $owner = User::factory()->create();
        [$trip] = $this->trip($owner);

        $this->actingAs($owner)->get(route('trips.show', $trip))->assertOk()
            ->assertSee('class="mc-meal"', false)             // per-meal price…
            ->assertSee('aria-label="Meals a day"', false)    // …× meals a day × days
            ->assertSee('aria-label="Meals total per person"', false);
    }
}
