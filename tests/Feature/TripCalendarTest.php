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

class TripCalendarTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Trip $trip;

    private Stop $lunch;

    private array $opts;

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner = User::factory()->create(['name' => 'Lea']);
        $this->trip = Trip::create(['slug' => 'kyoto', 'title' => 'Kyoto, autumn', 'destination' => 'Kyoto, Japan',
            'start_date' => '2027-11-10', 'end_date' => '2027-11-11', 'is_public' => false, 'created_by' => $this->owner->id]);
        $this->trip->members()->attach($this->owner->id, ['role' => 'owner']);
        $d1 = TripDay::create(['trip_id' => $this->trip->id, 'day_number' => 1, 'sort' => 1, 'date' => '2027-11-10', 'title' => 'Temples', 'hiccups' => []]);
        $d2 = TripDay::create(['trip_id' => $this->trip->id, 'day_number' => 2, 'sort' => 2, 'date' => '2027-11-11', 'title' => 'Arashiyama', 'hiccups' => []]);
        $d1->stops()->create(['sort' => 0, 'time' => '09:00–11:30', 'title' => 'Morning temple', 'has_options' => false]);
        $this->lunch = $d1->stops()->create(['sort' => 1, 'time' => '12:00', 'title' => 'Lunch', 'has_options' => true]);
        $d1->stops()->create(['sort' => 2, 'time' => '13:30', 'title' => 'Walk', 'has_options' => false]);
        $d2->stops()->create(['sort' => 0, 'time' => 'Evening', 'title' => 'Free time', 'has_options' => false]);
        $this->opts = [
            StopOption::create(['stop_id' => $this->lunch->id, 'sort' => 0, 'name' => 'Ramen, Gion', 'is_default_pick' => true, 'cost_min' => 10, 'cost_max' => 12]),
            StopOption::create(['stop_id' => $this->lunch->id, 'sort' => 1, 'name' => 'Kaiseki; set menu', 'note' => "Seasonal\ncourses"]),
        ];
    }

    private function ics(array $q = []): string
    {
        return $this->actingAs($this->owner)->get(route('trips.calendar', ['trip' => $this->trip] + $q))
            ->assertOk()->assertHeader('Content-Type', 'text/calendar; charset=utf-8')->getContent();
    }

    public function test_stops_become_timed_events_with_ranges_and_next_stop_ends(): void
    {
        $ics = $this->ics();
        $this->assertStringContainsString("DTSTART:20271110T090000\r\nDTEND:20271110T113000", $ics);  // explicit range
        $this->assertStringContainsString("DTSTART:20271110T120000\r\nDTEND:20271110T133000", $ics);  // until next stop
        $this->assertStringContainsString("DTSTART:20271110T133000\r\nDTEND:20271110T150000", $ics);  // last stop: 90 min
        $this->assertStringContainsString('SUMMARY:Day 2 · Arashiyama', $ics);                      // all-day banner
        $this->assertStringNotContainsString('Free time', $ics);                                     // no clock time → banner only
        $this->assertStringContainsString('SUMMARY:Lunch — Ramen\, Gion', $ics);                     // default pick, escaped
    }

    public function test_the_groups_pick_is_used_and_text_is_escaped(): void
    {
        TripPick::create(['trip_id' => $this->trip->id, 'stop_id' => $this->lunch->id, 'stop_option_id' => $this->opts[1]->id, 'picked_by' => $this->owner->id]);
        $ics = $this->ics();

        $this->assertStringContainsString('SUMMARY:Lunch — Kaiseki\; set menu', $ics);
        $this->assertStringContainsString('Seasonal\ncourses', $ics);
        $this->assertStringContainsString('Picked by Lea', $ics);
    }

    public function test_page_picks_are_honoured_but_only_for_options_of_that_stop(): void
    {
        $this->assertStringContainsString('Kaiseki', $this->ics(['picks' => "{$this->lunch->id}:{$this->opts[1]->id}"]));

        $other = StopOption::create(['stop_id' => Stop::where('title', 'Walk')->first()->id, 'sort' => 0, 'name' => 'Sneaky']);
        $ics = $this->ics(['picks' => "{$this->lunch->id}:{$other->id}"]);
        $this->assertStringNotContainsString('Lunch — Sneaky', $ics);   // another stop's option can't hijack Lunch
        $this->assertStringContainsString('Lunch — Ramen\, Gion', $ics);

        $this->actingAs($this->owner)->get(route('trips.calendar', ['trip' => $this->trip, 'picks' => 'drop table']))->assertSessionHasErrors('picks');
    }

    public function test_one_day_only(): void
    {
        $ics = $this->ics(['day' => 2]);
        $this->assertStringContainsString('Arashiyama', $ics);
        $this->assertStringNotContainsString('Morning temple', $ics);
        $this->assertStringContainsString('kyoto-autumn-day-2.ics', $this->actingAs($this->owner)->get(route('trips.calendar', ['trip' => $this->trip, 'day' => 2]))->headers->get('Content-Disposition'));
    }

    public function test_private_trips_are_not_exported_to_strangers(): void
    {
        $this->actingAs(User::factory()->create())->get(route('trips.calendar', $this->trip))->assertNotFound();
        $this->app['auth']->forgetGuards();
        $this->get(route('trips.calendar', $this->trip))->assertNotFound();
    }

    public function test_long_lines_are_folded(): void
    {
        $this->opts[0]->update(['note' => str_repeat('very long description ', 20)]);
        foreach (explode("\r\n", $this->ics()) as $line) {
            $this->assertLessThanOrEqual(75, strlen($line));
        }
    }
}
