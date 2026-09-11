<?php

namespace Tests\Feature;

use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TripBuilderTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_replace_recursive([
            'destination' => 'Kyoto, Japan',
            'title' => '',
            'party_size' => 2,
            'currency' => 'JPY',
            'arrival_date' => '2027-04-01',
            'departure_date' => '2027-04-05',
            'segments' => [
                ['from' => 'MNL', 'to' => 'KIX', 'depart' => '23:30', 'arrive' => '04:45 +1', 'airline' => 'PAL', 'flight_no' => 'PR 408'],
                ['from' => 'KIX', 'to' => 'MNL', 'depart' => '08:00', 'arrive' => '11:30', 'airline' => 'PAL', 'flight_no' => 'PR 409'],
            ],
            'hotel_name' => 'Hotel Granvia Kyoto',
            'hotel_address' => 'Karasuma-dori, Shimogyo Ward',
            'hotel_lat' => 34.9858,
            'hotel_lon' => 135.7588,
            'areas' => [
                ['name' => 'Higashiyama', 'lat' => 34.9948, 'lon' => 135.7850],
                ['name' => 'Arashiyama', 'lat' => 35.0094, 'lon' => 135.6667],
            ],
            'interests' => ['Food & drink', 'Temples & shrines'],
            'budget_per_person' => 1200,
            'shopping' => ['clothes' => 200, 'food' => 120],
        ], $overrides);
    }

    public function test_the_builder_requires_auth(): void
    {
        $this->get(route('trips.create'))->assertRedirect(route('login'));
        $this->post(route('trips.store'), $this->payload())->assertRedirect(route('login'));
    }

    public function test_it_builds_a_day_per_date_anchored_to_flights_hotel_and_areas(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('trips.store'), $this->payload())
            ->assertRedirect();

        $trip = Trip::where('created_by', $user->id)->firstOrFail();

        $this->assertFalse($trip->is_public);
        $this->assertSame('owner', $trip->roleFor($user));
        $this->assertSame('Hotel Granvia Kyoto', $trip->hotel_name);
        $this->assertEqualsWithDelta(34.9858, (float) $trip->lat, 0.001);
        $this->assertSame(['Food & drink', 'Temples & shrines'], $trip->interests['interests']);
        $this->assertContains('clothes', $trip->interests['shopping']);

        // 2027-04-01 .. 2027-04-05 inclusive = 5 days
        $this->assertSame(5, $trip->days()->count());

        $days = $trip->days()->orderBy('day_number')->get();
        $this->assertStringContainsString('Arrival', $days->first()->title);
        $this->assertSame('Departure day', $days->last()->title);
        // middle days take the area names, round-robin
        $this->assertSame('Higashiyama', $days[1]->title);
        $this->assertSame('Arashiyama', $days[2]->title);
        $this->assertEqualsWithDelta(35.0094, (float) $days[2]->lat, 0.001);

        // every day gets a keyless "today's area" map, anchored to its own coords
        $this->assertStringContainsString('openstreetmap.org', $days->first()->map_embed_url);
        $this->assertStringContainsString('34.9858%2C135.7588', $days->first()->map_embed_url); // hotel
        $this->assertStringContainsString('35.0094%2C135.6667', $days[2]->map_embed_url);        // Arashiyama

        // flight + hotel stops exist
        $this->assertStringContainsString('KIX', $days->first()->stops()->where('sort', 0)->value('title'));
        $this->assertStringContainsString('Granvia', $days->first()->stops()->where('sort', 1)->value('title'));

        // budget seeded, with a Shopping line per ticked category
        $this->assertGreaterThan(0, $trip->budgetLines()->where('category', 'Shopping')->count());
        $this->assertSame(200, (int) $trip->budgetLines()->where('label', 'Clothing')->value('amount'));

        // flight legs are also written to trip_segments for analytics — airports
        // resolve against config/airports.php by code; "PAL" isn't a recognized
        // airline code/name so it's kept as free text with no resolved code.
        $segments = $trip->tripSegments()->orderBy('sort')->get();
        $this->assertSame(2, $segments->count());
        $this->assertSame('MNL', $segments[0]->from_code);
        $this->assertSame('KIX', $segments[0]->to_code);
        $this->assertNull($segments[0]->airline_code);
        $this->assertSame('PAL', $segments[0]->airline_text);
        $this->assertTrue($segments[0]->date->isSameDay($trip->start_date));
        $this->assertTrue($segments[1]->date->isSameDay($trip->end_date));

        // the flight-pass "date shown" is derived from arrival/departure, not
        // free-typed — no 'date' key is even sent in the payload.
        $this->assertSame(strtoupper($trip->start_date->format('D d M Y')), $trip->segments[0]['date']);
        $this->assertSame(strtoupper($trip->end_date->format('D d M Y')), $trip->segments[1]['date']);
    }

    public function test_a_recognized_airline_resolves_to_its_code(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('trips.store'), $this->payload([
            'segments' => [
                ['from' => 'MNL', 'to' => 'KIX', 'airline' => 'Philippine Airlines', 'flight_no' => 'PR 408'],
                ['from' => 'KIX', 'to' => 'MNL', 'airline' => 'Philippine Airlines', 'flight_no' => 'PR 409'],
            ],
        ]));

        $trip = Trip::where('created_by', $user->id)->firstOrFail();
        $this->assertSame('PR', $trip->tripSegments()->first()->airline_code);
    }

    public function test_an_area_with_its_own_hotel_overrides_the_trip_hotel_for_that_day(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('trips.store'), $this->payload([
            'areas' => [
                ['name' => 'Higashiyama', 'lat' => 34.9948, 'lon' => 135.7850],
                [
                    'name' => 'Arashiyama', 'lat' => 35.0094, 'lon' => 135.6667,
                    'hotel_name' => 'Arashiyama Ryokan', 'hotel_address' => 'Arashiyama, Kyoto',
                    'hotel_lat' => 35.0100, 'hotel_lon' => 135.6700,
                ],
            ],
        ]));

        $trip = Trip::where('created_by', $user->id)->firstOrFail();
        $days = $trip->days()->orderBy('day_number')->get();

        // Higashiyama day has no hotel of its own — falls back to the trip's main hotel.
        $this->assertSame('Hotel Granvia Kyoto', $days[1]->hotel_name);
        // Arashiyama day uses its own hotel instead.
        $this->assertSame('Arashiyama Ryokan', $days[2]->hotel_name);
        $this->assertEqualsWithDelta(35.0100, (float) $days[2]->hotel_lat, 0.001);

        // Arrival anchors to the first area (Higashiyama, no hotel of its own)
        // so it falls back to the main hotel; departure anchors to the last
        // area (Arashiyama, which does have one) so it uses that instead —
        // the "fly out from the last city" case in a multi-city trip.
        $this->assertSame('Hotel Granvia Kyoto', $days->first()->hotel_name);
        $this->assertSame('Arashiyama Ryokan', $days->last()->hotel_name);
    }

    public function test_at_least_one_area_is_required(): void
    {
        $user = User::factory()->create();
        $payload = $this->payload();
        $payload['areas'] = [['name' => ''], ['name' => '  ']];

        $this->actingAs($user)
            ->from(route('trips.create'))
            ->post(route('trips.store'), $payload)
            ->assertRedirect(route('trips.create'))
            ->assertSessionHasErrors('areas');
    }

    public function test_departure_must_be_after_arrival(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('trips.create'))
            ->post(route('trips.store'), $this->payload(['departure_date' => '2027-03-30']))
            ->assertSessionHasErrors('departure_date');
    }
}
