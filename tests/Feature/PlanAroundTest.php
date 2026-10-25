<?php

namespace Tests\Feature;

use App\Jobs\DraftDayWithAi;
use App\Models\Trip;
use App\Models\User;
use App\Services\GenerateDayItinerary;
use App\Actions\IndexGeneratedPlaces;
use App\Services\StopPhotoFinder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanAroundTest extends TestCase
{
    use RefreshDatabase;

    private function make(array $extra = [], string $arrive = '23:30', string $depart = '07:10'): Trip
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('trips.store'), $extra + [
            'destination' => 'Kyoto, Japan', 'arrival_date' => '2027-04-01', 'departure_date' => '2027-04-04', 'party_size' => 2, 'currency' => 'PHP',
            'segments' => [['from' => 'MNL', 'arrive' => $arrive], ['to' => 'MNL', 'depart' => $depart]],
        ])->assertSessionHasNoErrors();

        return Trip::where('created_by', $user->id)->latest('id')->firstOrFail();
    }

    private function invokeSvc(string $method, ...$args)
    {
        $svc = app(GenerateDayItinerary::class);
        $m = new \ReflectionMethod($svc, $method);
        $m->setAccessible(true);

        return $m->invoke($svc, ...$args);
    }

    public function test_ticked_needs_pace_who_spend_are_stored_and_unknown_needs_rejected(): void
    {
        $trip = $this->make(['needs' => ['joints', 'heart_bp'], 'pace' => 'Relaxed', 'who' => 'Family', 'spend' => 'Mid-range']);
        $this->assertSame(['joints', 'heart_bp'], $trip->interests['needs']);
        $this->assertSame('Relaxed', $trip->interests['pace']);

        $this->actingAs(User::factory()->create())->post(route('trips.store'), [
            'destination' => 'Kyoto, Japan', 'arrival_date' => '2027-04-01', 'departure_date' => '2027-04-04',
            'segments' => [['from' => ''], ['to' => '']], 'needs' => ['my free-text diagnosis'],
        ])->assertSessionHasErrors('needs.0');
    }

    public function test_prompt_rules_follow_landing_time_flight_time_pace_and_needs_without_naming_conditions(): void
    {
        $trip = $this->make(['needs' => ['heart_bp'], 'pace' => 'Relaxed', 'spend' => 'Budget-friendly']);
        [$first, $mid, $last] = [$trip->days->first(), $trip->days->get(1), $trip->days->last()];

        $this->assertStringContainsString('ONE stop only', $this->invokeSvc('scheduleRule', $trip, $first)); // lands 23:30
        $this->assertStringContainsString('ONE stop only', $this->invokeSvc('scheduleRule', $trip, $last));  // flight 07:10
        $this->assertStringContainsString('3-4 stops', $this->invokeSvc('scheduleRule', $trip, $mid));

        $lines = $this->invokeSvc('travellerLines', $trip, $mid);
        $this->assertStringContainsString('no strenuous climbs', $lines);
        $this->assertStringContainsString('Budget-friendly', $lines);
        $this->assertStringNotContainsString('blood pressure', strtolower($lines));
        $this->assertStringNotContainsString('heart_bp', $lines);
    }

    public function test_afternoon_landing_and_evening_flight_leave_a_real_window(): void
    {
        $trip = $this->make([], '10:00', '21:00');
        $this->assertStringContainsString('none starting before 15:00', $this->invokeSvc('scheduleRule', $trip, $trip->days->first()));
        $this->assertStringContainsString('16:00 at the latest', $this->invokeSvc('scheduleRule', $trip, $trip->days->last()));
    }

    public function test_ai_redraft_keeps_landing_and_flight_stops_in_order(): void
    {
        $trip = $this->make([], '14:30', '21:15');
        $this->instance(GenerateDayItinerary::class, \Mockery::mock(GenerateDayItinerary::class, function ($m) {
            $m->shouldReceive('forDay')->andReturn([
                'weather_note' => 'x', 'weather_tag' => 'outdoor', 'temp_high' => null, 'temp_low' => null, 'outfit_chips' => [], 'summary' => 's', 'hiccups' => ['h'],
                'stops' => [['time' => '18:30', 'title' => 'Dinner', 'description' => '', 'cost_label' => null, 'option_label' => null, 'options' => []]],
            ]);
        }));

        foreach ([$trip->days->first(), $trip->days->last()] as $day) {
            (new DraftDayWithAi($day->id, false))->handle(app(GenerateDayItinerary::class), app(IndexGeneratedPlaces::class), app(StopPhotoFinder::class));
        }

        $first = $trip->days()->first()->stops()->orderBy('sort')->pluck('title')->all();
        $this->assertStringStartsWith('Land at', $first[0]);
        $this->assertStringStartsWith('Bag drop', $first[1]);
        $this->assertSame('Dinner', $first[2]);
        $this->assertSame('14:30', $trip->days()->first()->stops()->orderBy('sort')->first()->time);

        $last = $trip->days()->reorder('day_number', 'desc')->first()->stops()->orderBy('sort')->pluck('title')->all();
        $this->assertStringStartsWith('Checkout', $last[0]);
        $this->assertSame('Dinner', $last[1]);
        $this->assertStringStartsWith('Head to', $last[2]);
        $this->assertStringStartsWith('Depart', $last[3]);
        $this->assertCount(4, $last);
    }
}
