<?php

namespace Tests\Feature;

use App\Models\Trip;
use App\Models\TripDay;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DayLocationTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Trip $trip;

    private TripDay $day;

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner = User::factory()->create();
        $this->trip = Trip::create(['slug' => 'sf', 'title' => 'SF', 'destination' => 'San Francisco, USA', 'lat' => 37.77, 'lon' => -122.42,
            'start_date' => '2026-11-01', 'end_date' => '2026-11-02', 'is_public' => false, 'created_by' => $this->owner->id]);
        $this->trip->members()->attach($this->owner->id, ['role' => 'owner']);
        $this->day = TripDay::create(['trip_id' => $this->trip->id, 'day_number' => 1, 'sort' => 1, 'date' => '2026-11-01',
            'title' => 'Golden Gate', 'area_label' => 'Presidio', 'lat' => 37.79, 'lon' => -122.46,
            'hotel_name' => 'Hotel Zephyr', 'hotel_lat' => 37.81, 'hotel_lon' => -122.41, 'hiccups' => []]);
    }

    private function save(array $data)
    {
        return $this->actingAs($this->owner)->patch(route('trips.days.update', [$this->trip, $this->day]), $data + ['title' => 'Golden Gate']);
    }

    public function test_a_new_area_typed_without_picking_a_place_is_refused(): void
    {
        $this->save(['area_label' => 'Base: IBIS', 'hotel_name' => 'Hotel Zephyr'])->assertSessionHasErrors('area_label');
        $this->assertSame('Presidio', $this->day->fresh()->area_label);
    }

    public function test_a_picked_area_saves_its_coordinates_and_moves_the_map(): void
    {
        $this->save(['area_label' => 'Sausalito', 'lat' => 37.8591, 'lon' => -122.4853, 'hotel_name' => 'Hotel Zephyr'])->assertSessionHasNoErrors();
        $d = $this->day->fresh();
        $this->assertSame('Sausalito', $d->area_label);
        $this->assertEqualsWithDelta(37.8591, (float) $d->lat, 0.0001);
        $this->assertStringContainsString('openstreetmap.org', (string) $d->map_embed_url);
    }

    public function test_editing_only_the_title_keeps_the_existing_locations(): void
    {
        $this->save(['title' => 'Bridge day', 'area_label' => 'Presidio', 'hotel_name' => 'Hotel Zephyr'])->assertSessionHasNoErrors();
        $d = $this->day->fresh();
        $this->assertSame('Bridge day', $d->title);
        $this->assertEqualsWithDelta(37.79, (float) $d->lat, 0.0001);
        $this->assertEqualsWithDelta(37.81, (float) $d->hotel_lat, 0.0001);
    }

    public function test_a_new_hotel_must_be_picked_too(): void
    {
        $this->save(['area_label' => 'Presidio', 'hotel_name' => 'IBIS'])->assertSessionHasErrors('hotel_name');

        $this->save(['area_label' => 'Presidio', 'hotel_name' => 'ibis Styles SF', 'hotel_lat' => 37.785, 'hotel_lon' => -122.41,
            'hotel_address' => 'Union Square, San Francisco'])->assertSessionHasNoErrors();
        $this->assertSame('Union Square, San Francisco', $this->day->fresh()->hotel_address);
    }

    public function test_clearing_the_area_and_hotel_clears_their_coordinates(): void
    {
        $this->save(['area_label' => '', 'hotel_name' => ''])->assertSessionHasNoErrors();
        $d = $this->day->fresh();
        $this->assertNull($d->lat);
        $this->assertNull($d->hotel_lat);
        $this->assertNull($d->hotel_name);
    }

    public function test_the_edit_page_uses_place_search(): void
    {
        $this->actingAs($this->owner)->get(route('trips.days.edit', [$this->trip, $this->day]))
            ->assertOk()->assertSee('data-loc-input', false)->assertSee('Located on the map');
    }
}
