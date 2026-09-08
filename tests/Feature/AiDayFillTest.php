<?php

namespace Tests\Feature;

use App\Actions\DuplicateTrip;
use App\Models\Trip;
use App\Models\User;
use Database\Seeders\Seoul2026Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiDayFillTest extends TestCase
{
    use RefreshDatabase;

    private function ownedTrip(User $owner): Trip
    {
        $this->seed(Seoul2026Seeder::class);

        return app(DuplicateTrip::class)(Trip::where('slug', 'seoul-2026')->first(), $owner);
    }

    private function fakeClimateResponse()
    {
        return Http::response([
            'daily' => [
                'time' => ['2027-01-01', '2027-01-02', '2027-01-03'],
                'temperature_2m_max' => [18, 19, 17],
                'temperature_2m_min' => [9, 10, 8],
                'precipitation_sum' => [0, 3.2, 0],
            ],
        ]);
    }

    private function fakeGemini(): void
    {
        config(['services.gemini.api_key' => 'test-key', 'services.google.maps_key' => null]);

        Http::fake([
            'archive-api.open-meteo.com/*' => $this->fakeClimateResponse(),
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [[
                    'content' => ['parts' => [['text' => json_encode([
                        'weather_note' => 'Coastal and breezy — a jacket for the evening.',
                        'weather_tag' => 'outdoor',
                        'outfit_chips' => ['light layers', 'walking shoes'],
                        'summary' => 'A relaxed loop through the old town.',
                        'hiccups' => ['Museum shuts Mondays.', 'Last bus back is early.'],
                        'stops' => [
                            ['time' => '09:00', 'title' => 'Old town walk', 'description' => 'Start early.', 'options' => []],
                            ['time' => '12:30', 'title' => 'Lunch', 'option_label' => 'Where to eat', 'options' => [
                                ['name' => 'Budget diner', 'tier' => 'budget', 'note' => 'Fast and cheap.', 'cost_min' => 5, 'cost_max' => 8, 'map_query' => 'diner old town'],
                                ['name' => 'Mid bistro', 'tier' => 'mid', 'note' => 'Sit-down.', 'cost_min' => 15, 'cost_max' => 22],
                                ['name' => 'Splurge tasting', 'tier' => 'splurge', 'note' => 'Book ahead.', 'cost_min' => 60, 'cost_max' => 90],
                            ]],
                        ],
                    ])]]],
                ]],
            ]),
        ]);
    }

    public function test_editor_can_draft_a_day_and_it_replaces_the_stops(): void
    {
        $owner = User::factory()->create();
        $trip = $this->ownedTrip($owner);
        $day = $trip->days()->first();
        $this->fakeGemini();

        $this->actingAs($owner)
            ->post(route('trips.days.generate', [$trip, $day->id]))
            ->assertRedirect();

        $day->refresh();
        $this->assertSame('ai', $day->source);
        $this->assertSame('Coastal and breezy — a jacket for the evening.', $day->weather_note);
        $this->assertSame(['Museum shuts Mondays.', 'Last bus back is early.'], $day->hiccups);
        $this->assertSame(2, $day->stops()->count());

        // temps come from the (faked) historical climate average, not the model
        $this->assertSame(18, $day->temp_high); // round(avg(18,19,17))
        $this->assertSame(9, $day->temp_low);   // round(avg(9,10,8))

        $lunch = $day->stops()->where('title', 'Lunch')->first();
        $this->assertTrue($lunch->has_options);
        $this->assertSame(3, $lunch->options()->count());
        $this->assertSame('budget', $lunch->options()->orderBy('sort')->first()->tier);
    }

    public function test_a_viewer_cannot_draft_a_day(): void
    {
        $owner = User::factory()->create();
        $trip = $this->ownedTrip($owner);
        $viewer = User::factory()->create();
        $trip->members()->attach($viewer->id, ['role' => 'viewer']);
        $this->fakeGemini();

        $this->actingAs($viewer)
            ->post(route('trips.days.generate', [$trip, $trip->days()->first()->id]))
            ->assertForbidden();
    }

    public function test_it_503s_when_gemini_is_not_configured(): void
    {
        config(['services.gemini.api_key' => null]);
        $owner = User::factory()->create();
        $trip = $this->ownedTrip($owner);

        $this->actingAs($owner)
            ->post(route('trips.days.generate', [$trip, $trip->days()->first()->id]))
            ->assertStatus(503);
    }

    public function test_a_bad_gemini_response_leaves_the_day_untouched(): void
    {
        $owner = User::factory()->create();
        $trip = $this->ownedTrip($owner);
        $day = $trip->days()->first();
        $before = $day->stops()->count();

        config(['services.gemini.api_key' => 'test-key']);
        Http::fake([
            'archive-api.open-meteo.com/*' => $this->fakeClimateResponse(),
            'generativelanguage.googleapis.com/*' => Http::response(['candidates' => []], 200),
        ]);

        $this->actingAs($owner)
            ->post(route('trips.days.generate', [$trip, $day->id]))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame($before, $day->fresh()->stops()->count());
        $this->assertNotSame('ai', $day->fresh()->source);
    }
}
