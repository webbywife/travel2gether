<?php

namespace Tests\Feature;

use App\Models\Trip;
use App\Models\User;
use App\Support\Destinations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CurrencyTest extends TestCase
{
    use RefreshDatabase;

    private function trip(User $owner, string $currency): Trip
    {
        $trip = Trip::create([
            'slug' => 'fx-trip', 'title' => 'FX trip', 'destination' => 'Tokyo, Japan', 'currency' => $currency,
            'start_date' => '2026-11-01', 'end_date' => '2026-11-02', 'is_public' => false, 'created_by' => $owner->id,
        ]);
        $trip->members()->attach($owner->id, ['role' => 'owner']);
        $trip->budgetLines()->create(['sort' => 1, 'category' => 'Meals', 'label' => 'Food', 'amount' => 10000, 'per_person' => true]);
        $day = $trip->days()->create(['day_number' => 1, 'date' => '2026-11-01', 'title' => 'Day 1', 'hiccups' => ['x']]);
        $stop = $day->stops()->create(['sort' => 1, 'time' => '12:00', 'title' => 'Lunch']);
        $stop->options()->create(['sort' => 1, 'name' => 'Ramen', 'tier' => 'budget', 'cost_min' => 1000, 'cost_max' => 1500]);
        $stop->options()->create(['sort' => 2, 'name' => 'Imported', 'tier' => 'mid', 'cost_min' => 20, 'cost_max' => 30, 'currency' => 'USD']);

        return $trip;
    }

    public function test_the_budget_uses_the_live_rate_not_a_fixed_58(): void
    {
        $this->fxRates = ['JPY' => ['PHP' => 0.3986]];
        $owner = User::factory()->create();
        $trip = $this->trip($owner, 'JPY');

        $this->actingAs($owner)->get(route('trips.show', $trip))
            ->assertOk()
            ->assertSee('1 JPY in PHP')
            ->assertSee('value="0.3986"', false)
            ->assertSee('Live rate')
            ->assertDontSee('value="58"', false);
    }

    public function test_no_rate_means_a_dash_never_a_guess(): void
    {
        $owner = User::factory()->create();
        $trip = $this->trip($owner, 'JPY');

        $this->actingAs($owner)->get(route('trips.show', $trip))
            ->assertOk()
            ->assertSee("Couldn't load today's rate")
            ->assertDontSee('value="58"', false);
    }

    public function test_a_peso_trip_has_no_conversion_row(): void
    {
        $owner = User::factory()->create();
        $trip = $this->trip($owner, 'PHP');

        $this->actingAs($owner)->get(route('trips.show', $trip))->assertOk()->assertDontSee('id="fxrate"', false);
    }

    public function test_changing_the_currency_converts_the_amounts(): void
    {
        $this->fxRates = ['JPY' => ['HKD' => 0.05]];
        $owner = User::factory()->create();
        $trip = $this->trip($owner, 'JPY');

        $this->actingAs($owner)->patch(route('trips.update', $trip), ['destination' => 'Tokyo, Japan', 'currency' => 'HKD'])
            ->assertRedirect(route('trips.show', $trip));

        $trip->refresh()->load('budgetLines', 'days.stops.options');
        $this->assertSame('HKD', $trip->currency);
        $this->assertSame(500, $trip->budgetLines->first()->amount);
        $options = $trip->days->first()->stops->first()->options->keyBy('name');
        $this->assertSame([50, 75], [$options['Ramen']->cost_min, $options['Ramen']->cost_max]);
        $this->assertSame([20, 30], [$options['Imported']->cost_min, $options['Imported']->cost_max]); // own currency: untouched
    }

    public function test_no_rate_means_the_currency_is_not_changed(): void
    {
        $owner = User::factory()->create();
        $trip = $this->trip($owner, 'JPY');

        $this->actingAs($owner)->patch(route('trips.update', $trip), ['destination' => 'Tokyo, Japan', 'currency' => 'HKD'])
            ->assertSessionHasErrors('currency');

        $this->assertSame('JPY', $trip->refresh()->currency);
        $this->assertSame(10000, $trip->budgetLines()->first()->amount);
    }

    public function test_every_listed_country_has_a_plannable_local_currency(): void
    {
        foreach (Destinations::all() as $d) {
            $this->assertContains($d['currency'] ?? null, \App\Services\ExchangeRates::CURRENCIES, $d['country']);
        }
        $this->assertSame('HKD', Destinations::match('Hong Kong, China')['currency']);
    }
}
