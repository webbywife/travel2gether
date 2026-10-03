<?php

namespace Tests\Feature;

use App\Models\Trip;
use App\Models\TripDay;
use App\Models\User;
use App\Services\ClaudeClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;

class ClaudeAiTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Trip $trip;

    private TripDay $day;

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner = User::factory()->create();
        $this->trip = Trip::create([
            'slug' => 'cal2026', 'title' => 'California', 'destination' => 'San Francisco, USA',
            'start_date' => '2026-10-31', 'end_date' => '2026-11-07', 'is_public' => false,
            'created_by' => $this->owner->id, 'party_size' => 2, 'lat' => 37.77, 'lon' => -122.42,
        ]);
        $this->trip->members()->attach($this->owner->id, ['role' => 'owner']);
        foreach ([1, 2, 3] as $n) {
            $d = TripDay::create(['trip_id' => $this->trip->id, 'day_number' => $n, 'sort' => $n,
                'date' => "2026-11-0{$n}", 'title' => 'Golden gate Bridge', 'hiccups' => []]);
            $this->day ??= $d;
        }
    }

    private function claude(callable $expect): void
    {
        $this->mock(ClaudeClient::class, function (MockInterface $m) use ($expect) {
            $m->shouldReceive('enabled')->andReturn(true);
            $expect($m);
        });
    }

    public function test_the_helper_suggests_distinct_details_using_the_whole_trip(): void
    {
        $this->claude(fn ($m) => $m->shouldReceive('json')->once()
            ->withArgs(function ($system, $user) {
                // the whole trip is in the prompt, so repeats can be spotted
                return str_contains($user, 'Day 2') && str_contains($user, 'Day 3')
                    && str_contains($user, 'Muir Woods');
            })
            ->andReturn([
                'title' => 'Muir Woods & Sausalito', 'title_secondary' => '', 'area_label' => 'Marin County',
                'summary' => 'Redwoods in the morning, waterfront lunch.', 'weather_note' => 'Cool and foggy — bring layers.',
                'hotel_hint' => '', 'duplicate_note' => 'Days 1–3 are all "Golden gate Bridge" — vary them.',
            ]));

        $this->actingAs($this->owner)
            ->postJson(route('trips.days.suggest', [$this->trip, $this->day->id]), ['wish' => 'Muir Woods'])
            ->assertOk()
            ->assertJson(['title' => 'Muir Woods & Sausalito', 'area_label' => 'Marin County'])
            ->assertJsonPath('duplicate_note', 'Days 1–3 are all "Golden gate Bridge" — vary them.');

        // Suggestions only — nothing saved.
        $this->assertSame('Golden gate Bridge', $this->day->fresh()->title);
    }

    public function test_viewers_and_strangers_cannot_use_the_helper(): void
    {
        $this->claude(fn ($m) => $m->shouldNotReceive('json'));
        $viewer = User::factory()->create();
        $this->trip->members()->attach($viewer->id, ['role' => 'viewer']);
        $url = route('trips.days.suggest', [$this->trip, $this->day->id]);

        $this->actingAs($viewer)->postJson($url)->assertForbidden();
        $this->actingAs(User::factory()->create())->postJson($url)->assertForbidden();
        $this->app['auth']->forgetGuards(); // really signed out
        $this->postJson($url)->assertUnauthorized();
    }

    public function test_the_helper_reports_a_friendly_error_when_the_ai_fails(): void
    {
        $this->claude(fn ($m) => $m->shouldReceive('json')->andThrow(new \RuntimeException('unavailable')));

        $this->actingAs($this->owner)
            ->postJson(route('trips.days.suggest', [$this->trip, $this->day->id]))
            ->assertStatus(503)
            ->assertJsonPath('message', "The AI couldn't come up with suggestions just now — try again in a moment.");
    }

    public function test_the_edit_page_shows_the_helper(): void
    {
        $this->claude(fn ($m) => null);

        $this->actingAs($this->owner)
            ->get(route('trips.days.edit', [$this->trip, $this->day]))
            ->assertOk()
            ->assertSee('Help me with this day');
    }

    public function test_day_drafts_use_claude_with_a_structured_schema(): void
    {
        \Illuminate\Support\Facades\Http::fake(['*' => \Illuminate\Support\Facades\Http::response([], 200)]); // no real climate/places calls

        $this->claude(fn ($m) => $m->shouldReceive('json')->once()
            ->withArgs(fn ($system, $user, $schema) => ($schema['required'] ?? []) !== [] && in_array('stops', $schema['required'], true))
            ->andReturn([
                'weather_note' => 'Crisp and clear.', 'weather_tag' => 'outdoor', 'outfit_chips' => ['Layers'],
                'summary' => 'Big views.', 'hiccups' => ['Parking fills by 10am.'],
                'stops' => [[
                    'time' => '09:00', 'title' => 'Morning walk', 'description' => 'Start early', 'option_label' => 'Where',
                    'options' => [
                        ['name' => 'Lands End Trail', 'tier' => 'budget', 'note' => 'Coastal path', 'cost_min' => 0, 'cost_max' => 0, 'weather' => 'outdoor', 'map_query' => 'Lands End Trail SF'],
                        ['name' => 'de Young Museum', 'tier' => 'mid', 'note' => 'Art', 'cost_min' => 20, 'cost_max' => 20, 'weather' => 'indoor', 'map_query' => 'de Young'],
                    ],
                ]],
            ]));

        $this->actingAs($this->owner)
            ->post(route('trips.days.generate', [$this->trip, $this->day->id]))
            ->assertRedirect();

        $day = $this->day->fresh();
        $this->assertNull($day->ai_error, (string) $day->ai_error);
        $this->assertSame('ai', $day->source);
        $this->assertNull($day->ai_status);
        $this->assertSame('indoor', $day->stops()->first()->options()->where('name', 'de Young Museum')->first()->weather_tag);
    }

    public function test_trippie_answers_with_claude(): void
    {
        $this->claude(fn ($m) => $m->shouldReceive('chat')->once()
            ->withArgs(fn ($system, $messages) => end($messages)['content'] === 'Best time for Yosemite?' && $messages[0]['role'] === 'user')
            ->andReturn('[happy] Early morning, before the crowds!'));

        $this->postJson(route('trippie.chat'), [
            'message' => 'Best time for Yosemite?',
            'history' => [['role' => 'model', 'text' => 'Hi! I\'m Trippie.'], ['role' => 'user', 'text' => 'Hello']],
        ])->assertOk()->assertJson(['reply' => 'Early morning, before the crowds!', 'emotion' => 'happy']);
    }
}
