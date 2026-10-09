<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\BuildsSampleTrip;
use Illuminate\Database\Seeder;

/**
 * Public sample: a 4-day road trip from Las Vegas — Hoover Dam, the Grand
 * Canyon's South Rim, Horseshoe Bend and Antelope Canyon (Apr 2027).
 * Checked Oct 2026: park entry $35 per vehicle (7 days) plus, from 2026, a
 * $100 surcharge per non-US-resident aged 16+ at the busiest parks incl. the
 * Grand Canyon (or a $250 non-resident annual pass); Antelope Canyon is
 * guided-tour only (Navajo operators, ~$60–120 + permit); Horseshoe Bend
 * parking $10 (city lot, not covered by park passes); Page keeps Arizona time
 * while the surrounding Navajo Nation observes daylight saving.
 */
class GrandCanyon2027Seeder extends Seeder
{
    use BuildsSampleTrip;

    public function run(): void
    {
        $this->seedTrip([
            'slug' => 'grand-canyon-2027',
            'title' => 'Vegas → Grand Canyon → Antelope Canyon',
            'tagline' => 'Sample plan · road trip',
            'subhead' => 'Four days, one rental car: the Las Vegas Strip, Hoover Dam, sunset and sunrise on the Grand Canyon\'s South Rim, Horseshoe Bend and the light beams of Antelope Canyon. Swap any slot to fit your group.',
            'destination' => 'Grand Canyon National Park, USA',
            'origin_label' => 'MNL <-> LAS',
            'start_date' => '2027-04-15',
            'end_date' => '2027-04-18',
            'party_size' => 4,
            'currency' => 'USD',
            'lat' => 36.0544,   // Grand Canyon Village
            'lon' => -112.1401,
            'forecast_note' => 'Mid-April is a sweet spot: ~25 °C in Las Vegas, but the South Rim sits at ~2,100 m and can be near freezing at sunrise, with the odd spring snow. Pack for both. Live numbers fill in inside the ~16-day forecast window.',
            'segments' => [
                [
                    'from' => 'MNL', 'to' => 'LAS', 'date' => 'THU 15 APR 2027',
                    'depart' => '10:00', 'arrive' => '15:30', 'terminal' => 'NAIA 1 -> LAS (one stop)',
                    'airline' => 'Philippine Airlines + connection', 'flight_no' => '',
                ],
                [
                    'from' => 'LAS', 'to' => 'MNL', 'date' => 'SUN 18 APR 2027',
                    'depart' => '23:00', 'arrive' => '10:30 +2', 'terminal' => 'LAS -> NAIA 1 (one stop)',
                    'airline' => 'Philippine Airlines + connection', 'flight_no' => '',
                ],
            ],
            'stats' => [
                ['value' => '~1,000 km', 'label' => 'loop by car'],
                ['value' => '2', 'label' => 'canyons, very different'],
                ['value' => '$100', 'label' => 'new park surcharge per foreign visitor'],
                ['value' => '~0°', 'label' => 'at a South Rim sunrise'],
            ],
        ], $this->days(), $this->budgetLines());
    }

    private function days(): array
    {
        return [
            // ================= DAY 1 =================
            [
                'day_number' => 1,
                'date' => '2027-04-15',
                'title' => 'Land in Las Vegas, the Strip at night',
                'summary' => 'Pick up the car, check in, and walk the Strip after dark — the free shows are the best part.',
                'weather_tag' => 'outdoor',
                'forecast_date' => '2027-04-15',
                'temp_high' => 25,
                'temp_low' => 13,
                'lat' => 36.1147,
                'lon' => -115.1728,
                'weather_note' => 'Warm days, cool desert nights. Dry air — drink more water than you think.',
                'outfit_chips' => ['t-shirt', 'light jacket for the evening', 'comfortable shoes', 'sunglasses'],
                'area_label' => 'Las Vegas Strip',
                'map_embed_url' => $this->osm(36.1147, -115.1728, 0.03),
                'hiccups' => [
                    'The US needs a B1/B2 visitor visa for a Philippine passport, with an interview — apply months ahead and check the current fee.',
                    'Bring your Philippine driver\'s licence plus an International Driving Permit. Rental companies charge extra for drivers under 25 and need a credit card in the driver\'s name.',
                    'The Strip looks walkable but casinos are far apart — wear real shoes.',
                    'You\'re 15 hours behind Manila; keep tonight short — tomorrow is a 5-hour drive.',
                ],
                'stops' => [
                    [
                        'time' => '15:30', 'title' => 'Land at Las Vegas (LAS) — pick up the rental car', 'cost_label' => 'see budget',
                        'description' => 'A shuttle runs to the rental-car centre. An SUV or minivan for four with luggage.',
                    ],
                    [
                        'time' => '17:00', 'title' => 'Check in',
                        'option_label' => 'Where to stay',
                        'options' => [
                            $this->opt('Mid-Strip casino hotel', 'mid', 'walk to the fountains', 'Watch for the nightly "resort fee" on top of the room rate.', 50, 80, true),
                            $this->opt('Off-Strip hotel', 'budget', 'free parking, a short drive', 'Much cheaper; you have a car anyway.', 25, 45),
                            $this->opt('A suite on the Strip', 'splurge', 'one big room for four', 'Split four ways, the views are surprisingly affordable midweek.', 90, 150),
                        ],
                    ],
                    [
                        'time' => '19:00', 'title' => 'Dinner — a buffet or food hall', 'cost_label' => '~$20–70 food',
                        'description' => 'A Las Vegas buffet is the classic; the food halls are cheaper and quicker.',
                    ],
                    [
                        'time' => '20:30', 'title' => 'The free Strip shows', 'cost_label' => 'free',
                        'description' => 'The Bellagio fountains dance every 15 minutes in the evening, the Bellagio conservatory\'s seasonal garden is free indoors, and the Venetian\'s canals are a short walk away.',
                    ],
                ],
            ],

            // ================= DAY 2 =================
            [
                'day_number' => 2,
                'date' => '2027-04-16',
                'title' => 'Hoover Dam → Grand Canyon sunset',
                'summary' => 'Breakfast, Hoover Dam on the way out, Route 66 diners, and the first look over the South Rim at sunset.',
                'weather_tag' => 'outdoor',
                'forecast_date' => '2027-04-16',
                'temp_high' => 17,
                'temp_low' => 0,
                'lat' => 36.0619,
                'lon' => -112.1077,
                'weather_note' => 'You climb ~1,500 m today. Warm at the dam, cold at the rim after sunset.',
                'outfit_chips' => ['layers', 'warm jacket for the rim', 'beanie', 'hiking shoes'],
                'area_label' => 'Las Vegas → Hoover Dam → South Rim',
                'map_embed_url' => $this->osm(36.0619, -112.1077, 0.05),
                'hiccups' => [
                    'Grand Canyon entry is $35 per car — plus, since 2026, $100 for every non-US resident aged 16 and up. For four adults, compare that with the $250 non-resident annual pass.',
                    'Book Grand Canyon Village / Tusayan rooms months ahead — spring weekends sell out.',
                    'Mather Point is packed at sunset. Drive or take the free shuttle west to Hopi Point for a calmer view (Hermit Road is shuttle-only most of the year).',
                    'Fuel up in Williams or Tusayan — gas stations are far apart.',
                ],
                'stops' => [
                    [
                        'time' => '08:00', 'title' => 'Drive to Hoover Dam', 'cost_label' => 'parking fee',
                        'description' => '~45 min. Walk out on the Memorial Bridge for the view straight down onto the dam.',
                        'option_label' => 'How much dam',
                        'options' => [
                            $this->opt('Bridge walk + the dam top', 'budget', 'free walk, paid parking', 'The best photo is from the bridge. 45 minutes.', 10, 10, true),
                            $this->opt('Visitor Center', 'mid', 'exhibits and an overlook', 'An hour, with the story of how it was built.', 15, 15),
                            $this->opt('Guided dam tour', 'splurge', 'inside the tunnels', 'The deeper tour — limited places, book ahead.', 40, 40),
                        ],
                    ],
                    [
                        'time' => '10:00', 'title' => 'Drive to Williams, Arizona', 'cost_label' => 'fuel',
                        'description' => '~2.5 hours on I-40. Lunch in Williams, a Route 66 town of neon diners.',
                    ],
                    [
                        'time' => '14:30', 'title' => 'Into Grand Canyon National Park', 'cost_label' => '$35 car + $100 pp',
                        'description' => 'Another hour north. Check in, then the Visitor Center plaza for the shuttle map.',
                    ],
                    [
                        'time' => '16:00', 'title' => 'Rim Trail walk', 'cost_label' => 'free',
                        'description' => 'A flat paved path along the edge from Mather Point toward Yavapai Geology Museum — the canyon is a mile deep below you.',
                    ],
                    [
                        'time' => '18:30', 'title' => 'Sunset on the rim', 'cost_label' => 'free',
                        'description' => 'Hopi Point via the free shuttle, or Yavapai Point if you\'re tired. Then dinner in the village — it\'s cold, so go somewhere with soup.',
                    ],
                ],
            ],

            // ================= DAY 3 =================
            [
                'day_number' => 3,
                'date' => '2027-04-17',
                'title' => 'Sunrise, Desert View, Horseshoe Bend, Antelope Canyon',
                'summary' => 'Sunrise over the canyon, the scenic drive out past the Desert View Watchtower, then Page: the river\'s horseshoe and the slot canyon of light.',
                'weather_tag' => 'outdoor',
                'forecast_date' => '2027-04-17',
                'temp_high' => 22,
                'temp_low' => 2,
                'lat' => 36.8619,
                'lon' => -111.3743,
                'weather_note' => 'Freezing at sunrise, warm by the afternoon in Page. Dress in layers you can shed in the car.',
                'outfit_chips' => ['thermal + jacket for sunrise', 'sun hat', 'closed shoes for sand', 'small bag (big bags aren\'t allowed in the canyon)'],
                'area_label' => 'South Rim → Desert View → Page, Arizona',
                'map_embed_url' => $this->osm(36.5000, -111.8000, 0.3),
                'hiccups' => [
                    'Antelope Canyon can only be visited with a Navajo-authorised guided tour (~$60–120 a person plus a permit) — book weeks ahead; midday tours with light beams sell out first.',
                    'Clock trap: in summer time, Page stays on Arizona time while the Navajo Nation around it is an hour ahead. Confirm which time your tour is in.',
                    'Horseshoe Bend parking is $10 per car (a city lot — park passes don\'t cover it). The overlook is a 1 km walk each way with no shade.',
                    'Rain swap: flash-flood warnings cancel slot-canyon tours. If that happens, do the Glen Canyon Dam visitor centre and Lake Powell instead.',
                ],
                'stops' => [
                    [
                        'time' => '06:00', 'title' => 'Sunrise at Mather Point', 'cost_label' => 'free',
                        'description' => 'Quieter than sunset and colder — the layers pay off. Coffee from the market after.',
                    ],
                    [
                        'time' => '08:30', 'title' => 'Desert View Drive', 'cost_label' => 'free',
                        'description' => '~40 km east along the rim with viewpoints, ending at the Desert View Watchtower, a stone tower built in 1932 with painted interiors. Leave the park by the east entrance.',
                    ],
                    [
                        'time' => '10:30', 'title' => 'Drive to Page', 'cost_label' => 'fuel',
                        'description' => '~2 hours across the Navajo Nation. Roadside stands sell Navajo jewellery and fry bread.',
                    ],
                    [
                        'time' => '12:30', 'title' => 'Antelope Canyon tour', 'cost_label' => '$60–120+',
                        'option_label' => 'Which canyon',
                        'options' => [
                            $this->opt('Lower Antelope Canyon', 'mid', '~1 hr, ladders and stairs', 'Narrower and more twisting, with metal stairs in and out. Usually a little cheaper and easier to book.', 60, 85, true),
                            $this->opt('Upper Antelope Canyon', 'splurge', 'flat walk, midday light beams', 'The famous shafts of light around noon, March to October. Pricier, sells out.', 85, 120),
                            $this->opt('Antelope Canyon X or a water tour', 'budget', 'less crowded alternatives', 'Other slot canyons on Navajo land, or a boat on Lake Powell into the canyon\'s lower end.', 55, 90),
                        ],
                    ],
                    [
                        'time' => '16:30', 'title' => 'Horseshoe Bend at golden hour', 'cost_label' => '$10 parking',
                        'description' => 'The Colorado River making a near-perfect loop 300 m below. Stay back from the unfenced edges.',
                    ],
                    [
                        'time' => '19:00', 'title' => 'Dinner + night in Page', 'cost_label' => '~$15–30 food',
                        'description' => 'Page has the motels and a few good restaurants — a cowboy steakhouse is the local thing.',
                    ],
                ],
            ],

            // ================= DAY 4 =================
            [
                'day_number' => 4,
                'date' => '2027-04-18',
                'title' => 'Back to Las Vegas, fly home',
                'summary' => 'An easy drive back through the desert, with an optional detour, and a late flight out of Las Vegas.',
                'weather_tag' => 'covered',
                'forecast_date' => '2027-04-18',
                'temp_high' => 25,
                'temp_low' => 12,
                'lat' => 36.1147,
                'lon' => -115.1728,
                'weather_note' => 'Mostly in the car. Keep water and snacks within reach.',
                'outfit_chips' => ['comfy travel clothes', 'a layer for the plane'],
                'area_label' => 'Page → Las Vegas → LAS',
                'map_embed_url' => $this->osm(36.5000, -113.3000, 0.6),
                'hiccups' => [
                    'Page to Las Vegas is ~4.5 hours without stops. Return the car with time to spare — the rental centre shuttle adds ~30 minutes.',
                    'Utah\'s roads look tempting on the map: a Zion detour adds 2–3 hours and needs another park fee (and the $100 surcharge too, if it\'s on the list).',
                    'Nevada and Arizona are on the same clock in summer time, so no surprises on the way back.',
                ],
                'stops' => [
                    [
                        'time' => '08:00', 'title' => 'Breakfast and check out', 'cost_label' => '~$10–20 food',
                    ],
                    [
                        'time' => '09:00', 'title' => 'The drive back', 'cost_label' => 'fuel',
                        'option_label' => 'Which way',
                        'options' => [
                            $this->opt('Straight back via Kanab, Utah', 'budget', '~4.5 hrs', 'The fastest route — red cliffs the whole way.', 0, 0, true),
                            $this->opt('Detour through Zion', 'splurge', '+2–3 hrs, park fee', 'Drive the Zion–Mount Carmel highway through the tunnel. Only if everyone has energy left.', 35, 35),
                            $this->opt('Valley of Fire on the way in', 'mid', 'a state park near Vegas, ~1 hr extra', 'Red sandstone and petroglyphs — a cheaper, quicker last stop.', 10, 15),
                        ],
                    ],
                    [
                        'time' => '15:00', 'title' => 'Last stop: outlets or the Welcome sign', 'cost_label' => '~$0–200',
                        'description' => 'The "Welcome to Fabulous Las Vegas" sign for the group photo (free parking lot), then the Las Vegas outlets for pasalubong.',
                    ],
                    [
                        'time' => '19:30', 'title' => 'Return the car → LAS', 'cost_label' => 'free',
                        'description' => 'Fill the tank first to avoid the refuelling charge.',
                    ],
                    [
                        'time' => '23:00', 'title' => 'Fly home to Manila',
                        'description' => 'One stop. Trip complete.',
                    ],
                ],
            ],
        ];
    }

    private function budgetLines(): array
    {
        return [
            ['category' => 'Transpo', 'label' => 'Round-trip airfare', 'note' => 'MNL ⇄ LAS, one stop', 'amount' => 1300],
            ['category' => 'Transpo', 'label' => 'PH travel tax + terminal fee', 'amount' => 35],
            ['category' => 'Transpo', 'label' => 'Rental SUV, 4 days + insurance', 'note' => 'split 4 ways', 'amount' => 120],
            ['category' => 'Transpo', 'label' => 'Fuel', 'note' => '~1,000 km, split 4 ways', 'amount' => 35],
            ['category' => 'Accommodation', 'label' => 'Las Vegas, Grand Canyon, Page', 'note' => '3 nights, quad rooms ÷ 4, incl. resort fee', 'amount' => 260],
            ['category' => 'Meals', 'label' => 'Per diem', 'note' => '~$20 / meal × 3 × 4 days', 'amount' => 240, 'per_person' => true],
            ['category' => 'Rail & entry', 'label' => 'Grand Canyon non-resident surcharge', 'note' => '$100 per person 16+ (or a $250 annual pass)', 'amount' => 100],
            ['category' => 'Rail & entry', 'label' => 'Park car fee + Horseshoe Bend parking', 'note' => 'split 4 ways', 'amount' => 12],
            ['category' => 'Rail & entry', 'label' => 'Antelope Canyon tour', 'amount' => 90],
            ['category' => 'Insurance', 'label' => 'Travel insurance', 'note' => 'US healthcare is expensive — get medical cover', 'amount' => 40],
            ['category' => 'Communication', 'label' => 'eSIM', 'note' => 'coverage is patchy in the parks', 'amount' => 15],
            ['category' => 'Visa', 'label' => 'US B1/B2 visa', 'note' => 'check the current fee', 'amount' => 185],
        ];
    }
}
