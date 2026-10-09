<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\BuildsSampleTrip;
use Illuminate\Database\Seeder;

/**
 * Public sample: New York at Christmas (3–7 Dec 2026, right after the
 * Rockefeller tree lighting on 2 Dec). Checked Oct 2026: subway/bus $3 by
 * tap (OMNY; MetroCard ended 2025), fares cap after $35 in 7 days; Top of the
 * Rock $47–60; Statue of Liberty ferry $25.50; 9/11 Memorial free, museum $33;
 * the Met $30 for non-New Yorkers.
 */
class NewYork2026Seeder extends Seeder
{
    use BuildsSampleTrip;

    public function run(): void
    {
        $this->seedTrip([
            'slug' => 'new-york-2026',
            'title' => 'New York at Christmas',
            'tagline' => 'Sample plan · 5 days',
            'subhead' => 'New York in its holiday lights: the Rockefeller tree, skating in Bryant Park, the Statue of Liberty by ferry, Central Park, a Broadway night and the over-the-top Christmas houses of Dyker Heights. Tap any slot to make it your group\'s.',
            'destination' => 'New York City, USA',
            'origin_label' => 'MNL <-> JFK',
            'start_date' => '2026-12-03',
            'end_date' => '2026-12-07',
            'party_size' => 4,
            'currency' => 'USD',
            'lat' => 40.7549,   // Midtown
            'lon' => -73.9840,
            'forecast_note' => 'Early December in New York: ~7 °C by day, around freezing at night, and short days — dark by 16:30. Proper winter clothes, not a Manila jacket. Live numbers fill in inside the ~16-day forecast window.',
            'segments' => [
                [
                    'from' => 'MNL', 'to' => 'JFK', 'date' => 'THU 03 DEC 2026',
                    'depart' => '19:00', 'arrive' => '22:00', 'terminal' => 'NAIA 1 -> JFK',
                    'airline' => 'Philippine Airlines', 'flight_no' => '',
                ],
                [
                    'from' => 'JFK', 'to' => 'MNL', 'date' => 'MON 07 DEC 2026',
                    'depart' => '23:55', 'arrive' => '05:55 +2', 'terminal' => 'JFK -> NAIA 1',
                    'airline' => 'Philippine Airlines', 'flight_no' => '',
                ],
            ],
            'stats' => [
                ['value' => '~16h', 'label' => 'nonstop flight'],
                ['value' => '$3', 'label' => 'a subway ride (tap your card)'],
                ['value' => '5', 'label' => 'boroughs — we do 3'],
                ['value' => '~7°', 'label' => 'daytime high — real coat'],
            ],
        ], $this->days(), $this->budgetLines());
    }

    private function days(): array
    {
        return [
            // ================= DAY 1 =================
            [
                'day_number' => 1,
                'date' => '2026-12-03',
                'title' => 'Land at JFK, straight to bed',
                'summary' => 'You land the same evening you left, after ~16 hours. Get to the hotel, eat something, sleep — tomorrow starts the real trip.',
                'weather_tag' => 'indoor',
                'forecast_date' => '2026-12-03',
                'temp_high' => 7,
                'temp_low' => 0,
                'lat' => 40.6413,
                'lon' => -73.7781,
                'weather_note' => 'Near freezing at night — have the coat in your hand luggage, not the checked bag.',
                'outfit_chips' => ['winter coat', 'thermal layer', 'beanie + gloves', 'closed shoes'],
                'area_label' => 'JFK → Midtown Manhattan',
                'map_embed_url' => $this->osm(40.7000, -73.8900, 0.12),
                'hiccups' => [
                    'The US needs a B1/B2 visitor visa for a Philippine passport, with an interview — apply months ahead; a new visa-integrity fee is pushing the total cost up, so check the current fee.',
                    'MetroCards are gone: tap a contactless card or phone at the subway gate ($3). After 12 paid rides in 7 days the rest of the week is free (a $35 cap).',
                    'The AirTrain from JFK charges its own fee on top of the subway fare — with four people and luggage late at night, a car is easier.',
                    'Jet lag: New York is 13 hours behind Manila. Stay up until ~21:00 local tomorrow.',
                ],
                'stops' => [
                    [
                        'time' => '22:00', 'title' => 'Land at JFK', 'cost_label' => 'free',
                        'description' => 'Immigration can take an hour at night. Keep your hotel address and return ticket handy for the officer.',
                    ],
                    [
                        'time' => '23:00', 'title' => 'Into Manhattan', 'cost_label' => '~$12–100',
                        'option_label' => 'Getting to the hotel',
                        'options' => [
                            $this->opt('Yellow cab — flat fare to Manhattan', 'mid', '~45–60 min · flat fare plus tolls, surcharges and tip', 'Split four ways it\'s close to the train, and it\'s door to door.', 25, 30, true),
                            $this->opt('AirTrain + subway', 'budget', '~75 min · AirTrain fee + $3 subway', 'Cheapest, but stairs and transfers with big suitcases at midnight.', 12, 12),
                            $this->opt('Pre-booked car service', 'splurge', 'driver waits at arrivals', 'Worth it if anyone in the group is a nervous first-timer.', 30, 40),
                        ],
                    ],
                    [
                        'time' => '00:00', 'title' => 'Check in — Midtown',
                        'description' => 'Midtown puts Rockefeller Center, Times Square, Bryant Park and most subway lines within walking distance. A deli sandwich on the way up.',
                        'weather_tag' => 'indoor',
                    ],
                ],
            ],

            // ================= DAY 2 =================
            [
                'day_number' => 2,
                'date' => '2026-12-04',
                'title' => 'Midtown in its Christmas lights',
                'summary' => 'Fifth Avenue windows, the tree at Rockefeller Center, skating in Bryant Park and the city from the top of a skyscraper at sunset.',
                'weather_tag' => 'outdoor',
                'forecast_date' => '2026-12-04',
                'temp_high' => 7,
                'temp_low' => 0,
                'lat' => 40.7587,
                'lon' => -73.9787,
                'weather_note' => 'Cold and bright most December days. Duck into shops and lobbies to warm up — every block has one.',
                'outfit_chips' => ['winter coat', 'thermal top', 'scarf', 'gloves (touchscreen)', 'comfortable boots'],
                'area_label' => 'Midtown — Fifth Avenue to Times Square',
                'map_embed_url' => $this->osm(40.7587, -73.9787, 0.02),
                'hiccups' => [
                    'Rockefeller Center is shoulder to shoulder from ~17:00 to 21:00. See the tree in the morning, come back at night only for the lights.',
                    'Top of the Rock is timed-entry and $47–60 depending on the hour — sunset slots sell out first in December, so book before you fly.',
                    'Bryant Park\'s rink is free to enter, but skate rental costs extra and weekend evenings have queues.',
                    'Sunset is around 16:30 — plan the sightseeing that needs daylight before 16:00.',
                ],
                'stops' => [
                    [
                        'time' => '08:30', 'title' => 'Breakfast — bagels', 'cost_label' => '~$8–15 food',
                        'description' => 'A New York bagel with cream cheese and coffee. Get it to go and walk.',
                    ],
                    [
                        'time' => '09:30', 'title' => 'Rockefeller tree + St Patrick\'s Cathedral', 'cost_label' => 'free',
                        'description' => 'The tree and the skating rink below it without the evening crush, then the cathedral across Fifth Avenue.',
                    ],
                    [
                        'time' => '10:30', 'title' => 'Fifth Avenue holiday windows', 'cost_label' => 'free',
                        'description' => 'The department-store windows are a show of their own — walk from Saks up toward Central Park South.',
                    ],
                    [
                        'time' => '12:30', 'title' => 'Lunch', 'cost_label' => '~$12–40 food',
                        'option_label' => 'Lunch',
                        'options' => [
                            $this->opt('Halal cart + hot dogs', 'budget', 'street food, eaten standing', 'Chicken over rice from a cart — a New York rite.', 10, 15, true),
                            $this->opt('Food hall', 'mid', 'Urbanspace or the Plaza Food Hall', 'Warm, seated, everyone picks their own.', 18, 28),
                            $this->opt('Classic deli', 'splurge', 'pastrami on rye', 'Huge sandwiches — one feeds two.', 30, 40),
                        ],
                    ],
                    [
                        'time' => '14:00', 'title' => 'Bryant Park Winter Village', 'cost_label' => 'free–$40',
                        'description' => 'The free skating rink and the holiday shops in little glass booths, under the New York Public Library. Pop into the library\'s Rose Reading Room to warm up.',
                    ],
                    [
                        'time' => '16:00', 'title' => 'Sunset from above', 'cost_label' => '$45–60',
                        'option_label' => 'Which view',
                        'options' => [
                            $this->opt('Top of the Rock', 'mid', 'timed entry, $47–60', 'The view with the Empire State Building in it — and the tree right below.', 47, 60, true),
                            $this->opt('Empire State Building', 'mid', 'the classic', 'The 86th-floor open-air deck — cold, iconic.', 45, 55),
                            $this->opt('Skip it — Times Square lights instead', 'budget', 'free', 'The ball, the billboards, the crowds. 20 minutes is enough.', 0, 0),
                        ],
                    ],
                    [
                        'time' => '19:00', 'title' => 'The tree, lit up', 'cost_label' => 'free',
                        'description' => 'Back to Rockefeller Center for the tree after dark — and the light show on the facade of Saks across the street, if it\'s running this year.',
                    ],
                ],
            ],

            // ================= DAY 3 =================
            [
                'day_number' => 3,
                'date' => '2026-12-05',
                'title' => 'Lady Liberty, downtown, Brooklyn Bridge',
                'summary' => 'Ferry to the Statue of Liberty, the 9/11 Memorial pools, then walk across the Brooklyn Bridge to DUMBO for the skyline at dusk.',
                'weather_tag' => 'outdoor',
                'forecast_date' => '2026-12-05',
                'temp_high' => 6,
                'temp_low' => -1,
                'lat' => 40.7033,
                'lon' => -74.0170,
                'weather_note' => 'The harbour is windier than Midtown — the warmest layer goes on today.',
                'outfit_chips' => ['windproof coat', 'beanie', 'gloves', 'walking shoes'],
                'area_label' => 'Lower Manhattan → Brooklyn',
                'map_embed_url' => $this->osm(40.7033, -74.0100, 0.03),
                'hiccups' => [
                    'The Statue of Liberty ferry is $25.50 and has airport-style security — take the first ferries of the day. Pedestal and crown access need separate tickets, and the crown sells out months ahead.',
                    'The 9/11 Memorial pools are free; the museum is $33 — allow 2 hours if you go in.',
                    'The free Staten Island Ferry passes the statue without stopping — a good budget swap if the paid ferry is sold out.',
                    'Rain swap: the 9/11 Museum and Oculus in the morning, then the Brooklyn Bridge only if it clears.',
                ],
                'stops' => [
                    [
                        'time' => '08:30', 'title' => 'See the Statue of Liberty', 'cost_label' => 'free–$26',
                        'option_label' => 'How close',
                        'options' => [
                            $this->opt('Ferry to Liberty + Ellis Island', 'mid', 'from Battery Park · $25.50', 'Walk around the statue, then the immigration museum on Ellis Island. Half a day.', 26, 26, true),
                            $this->opt('Staten Island Ferry', 'budget', 'free, ~25 min each way', 'Passes close to the statue with the skyline behind you. An hour round trip.', 0, 0),
                            $this->opt('Ferry + pedestal access', 'splurge', 'book ahead', 'Climb into the pedestal for the view over the harbour.', 26, 30),
                        ],
                    ],
                    [
                        'time' => '13:00', 'title' => 'Lunch near Wall Street', 'cost_label' => '~$12–30 food',
                        'description' => 'Stone Street\'s cobbled lane of pubs, or the food stalls at the Oculus.',
                    ],
                    [
                        'time' => '14:00', 'title' => '9/11 Memorial + the Oculus', 'cost_label' => 'free ($33 museum)',
                        'description' => 'The two reflecting pools in the footprints of the towers, with every name cut into the bronze. The white-ribbed Oculus station next door.',
                    ],
                    [
                        'time' => '15:30', 'title' => 'Walk the Brooklyn Bridge', 'cost_label' => 'free',
                        'description' => '~30 minutes across on the raised walkway — the skyline gets better behind you, so stop and look back.',
                    ],
                    [
                        'time' => '16:15', 'title' => 'DUMBO at dusk', 'cost_label' => 'free',
                        'description' => 'The Manhattan Bridge framed between old warehouses on Washington Street, then the waterfront park as the skyline lights up.',
                    ],
                    [
                        'time' => '18:00', 'title' => 'Dinner — New York pizza', 'cost_label' => '~$8–30 food',
                        'description' => 'Brooklyn has the famous coal-oven pizzerias by the bridge (expect a queue), or a $4 slice anywhere. Subway back to Midtown.',
                    ],
                ],
            ],

            // ================= DAY 4 =================
            [
                'day_number' => 4,
                'date' => '2026-12-06',
                'title' => 'Central Park, the Met, a Broadway night',
                'summary' => 'A winter walk through Central Park, a museum to warm up in, then the night is yours: Broadway, or the Christmas houses of Dyker Heights.',
                'weather_tag' => 'outdoor',
                'forecast_date' => '2026-12-06',
                'temp_high' => 7,
                'temp_low' => 0,
                'lat' => 40.7794,
                'lon' => -73.9632,
                'weather_note' => 'A mostly indoor afternoon — the museum is the warm-up.',
                'outfit_chips' => ['coat', 'comfortable shoes for museum floors', 'a nicer layer for Broadway'],
                'area_label' => 'Central Park → Upper East Side → Theater District',
                'map_embed_url' => $this->osm(40.7740, -73.9700, 0.03),
                'hiccups' => [
                    'The Met is $30 for visitors from outside New York State (pay-what-you-wish is for NY residents only).',
                    'TKTS in Times Square sells same-day Broadway tickets at a discount — the line is shortest right when it opens; the biggest hits rarely appear there.',
                    'Dyker Heights is a ~1-hour subway ride to Brooklyn and the lights are on from dusk — go after 17:00 and go together.',
                ],
                'stops' => [
                    [
                        'time' => '09:00', 'title' => 'Central Park in winter', 'cost_label' => 'free',
                        'description' => 'In at Columbus Circle: the Mall\'s tree-lined avenue, Bethesda Terrace and Fountain, Bow Bridge, and the Wollman ice rink with the skyline behind it.',
                    ],
                    [
                        'time' => '11:30', 'title' => 'A museum to warm up in', 'cost_label' => '$25–30',
                        'option_label' => 'Which museum',
                        'options' => [
                            $this->opt('The Metropolitan Museum of Art', 'mid', '$30 for non-New Yorkers', 'Egypt\'s Temple of Dendur, the armour hall, and the rooftop if it\'s open. Pick 3 sections, not 30.', 30, 30, true),
                            $this->opt('American Museum of Natural History', 'mid', 'west side of the park', 'The dinosaurs and the blue whale — the pick for families.', 28, 30),
                            $this->opt('Skip it — Grand Central + the library', 'budget', 'free', 'The Grand Central main concourse and the library\'s reading room, both free and warm.', 0, 0),
                        ],
                    ],
                    [
                        'time' => '14:30', 'title' => 'Late lunch', 'cost_label' => '~$15–35 food',
                        'description' => 'Upper East Side diner, or back toward Midtown for ramen in the cold.',
                    ],
                    [
                        'time' => '17:00', 'title' => 'Your night', 'cost_label' => '$0–200',
                        'option_label' => 'Pick one',
                        'options' => [
                            $this->opt('A Broadway show', 'splurge', 'TKTS same-day, or book ahead', 'The one splurge New York is famous for. Dinner before, near Times Square.', 90, 200, true),
                            $this->opt('Dyker Heights Christmas lights', 'budget', 'subway to Brooklyn, free', 'Whole streets of houses competing with lights, giant toy soldiers and inflatables. Hot chocolate from the vendors.', 6, 15),
                            $this->opt('Radio City Christmas Spectacular', 'splurge', 'the Rockettes', 'The high-kicking holiday show — a December-only classic.', 80, 180),
                        ],
                    ],
                ],
            ],

            // ================= DAY 5 =================
            [
                'day_number' => 5,
                'date' => '2026-12-07',
                'title' => 'High Line, last shopping, fly home',
                'summary' => 'Check out, walk the old elevated railway to Chelsea Market, do the pasalubong run and head to JFK in the evening.',
                'weather_tag' => 'outdoor',
                'forecast_date' => '2026-12-07',
                'temp_high' => 7,
                'temp_low' => 0,
                'lat' => 40.7420,
                'lon' => -74.0048,
                'weather_note' => 'An easy day. Keep the coat on top of the suitcase.',
                'outfit_chips' => ['comfy travel layers', 'coat', 'sneakers'],
                'area_label' => 'Chelsea → Midtown → JFK',
                'map_embed_url' => $this->osm(40.7420, -74.0048, 0.025),
                'hiccups' => [
                    'Leave Midtown for JFK by ~19:30 for a 23:55 flight — international check-in closes about an hour before, and Monday-evening traffic to Queens is slow.',
                    'Hotels hold bags after checkout; ask for the claim ticket.',
                    'Sales tax isn\'t refunded to tourists in New York — the price on the tag is not what you pay.',
                ],
                'stops' => [
                    [
                        'time' => '09:00', 'title' => 'Checkout — bags held at the hotel',
                    ],
                    [
                        'time' => '10:00', 'title' => 'The High Line', 'cost_label' => 'free',
                        'description' => 'A park on a 1930s elevated freight railway, 2.3 km from Hudson Yards (the Vessel) down to the Meatpacking District.',
                    ],
                    [
                        'time' => '11:30', 'title' => 'Chelsea Market', 'cost_label' => '~$12–30 food',
                        'description' => 'An old biscuit factory turned food hall — tacos, lobster rolls, doughnuts. Lunch here.',
                        'weather_tag' => 'indoor',
                    ],
                    [
                        'time' => '13:30', 'title' => 'Pasalubong run', 'cost_label' => '~$30–200',
                        'option_label' => 'Where to shop',
                        'options' => [
                            $this->opt('Macy\'s Herald Square + Midtown', 'mid', 'the giant department store', 'Everything under one roof, plus the Christmas floors.', 50, 200, true),
                            $this->opt('Souvenir and snack shops', 'budget', 'I ❤ NY shirts, Hershey\'s, M&M\'s stores', 'The classic take-home bag for the family.', 30, 80),
                            $this->opt('Woodbury Common outlets', 'splurge', 'day-trip bus, ~1 hr each way', 'Only if shopping is the point of the trip — it eats the whole day.', 100, 400),
                        ],
                    ],
                    [
                        'time' => '19:30', 'title' => 'Collect bags → JFK', 'cost_label' => '~$12–100',
                        'description' => 'Same choices as Day 1, in reverse.',
                    ],
                    [
                        'time' => '23:55', 'title' => 'Fly home to Manila',
                        'description' => 'Lands early on Wednesday morning, Manila time. Trip complete.',
                    ],
                ],
            ],
        ];
    }

    private function budgetLines(): array
    {
        return [
            ['category' => 'Transpo', 'label' => 'Round-trip airfare', 'note' => 'MNL ⇄ JFK nonstop, December', 'amount' => 1400],
            ['category' => 'Transpo', 'label' => 'PH travel tax + terminal fee', 'amount' => 35],
            ['category' => 'Transpo', 'label' => 'Subway', 'note' => '$3 a ride, capped at $35 a week', 'amount' => 35],
            ['category' => 'Transpo', 'label' => 'Airport transfers', 'note' => 'cab split 4 ways, both ways', 'amount' => 60],
            ['category' => 'Accommodation', 'label' => 'Midtown hotel', 'note' => 'December rate, quad ÷ 4 × 4 nights', 'amount' => 500],
            ['category' => 'Meals', 'label' => 'Per diem', 'note' => '~$20 / meal × 3 × 5 days', 'amount' => 300, 'per_person' => true],
            ['category' => 'Rail & entry', 'label' => 'Top of the Rock, Liberty ferry, the Met', 'amount' => 115],
            ['category' => 'Rail & entry', 'label' => 'Broadway show', 'note' => 'optional', 'amount' => 120],
            ['category' => 'Shopping', 'label' => 'Pasalubong + winter gear', 'amount' => 150],
            ['category' => 'Insurance', 'label' => 'Travel insurance', 'note' => 'US healthcare is expensive — get medical cover', 'amount' => 40],
            ['category' => 'Communication', 'label' => 'eSIM', 'note' => '~5 days', 'amount' => 15],
            ['category' => 'Visa', 'label' => 'US B1/B2 visa', 'note' => 'check the current fee', 'amount' => 185],
        ];
    }
}
