<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\BuildsSampleTrip;
use Illuminate\Database\Seeder;

/**
 * Public sample: Kyoto in autumn-leaf season (late Nov 2026). Checked Oct
 * 2026: Kiyomizu-dera ¥500, night viewing 21–30 Nov until 21:30; Kinkaku-ji
 * ¥500 9:00–17:00; Tenryū-ji garden ¥500 8:30–17:00; Ōkōchi Sansō ¥1,000 with
 * matcha; Ginkaku-ji ¥500; Fushimi Inari 24 h, free; HARUKA ~75 min, ¥3,060
 * unreserved (the ICOCA & HARUKA combo ended 2023); city bus ¥230; Gion
 * private-lane photo ban (¥10,000); no photos on Tsūtenkyō in autumn.
 */
class Kyoto2026Seeder extends Seeder
{
    use BuildsSampleTrip;

    public function run(): void
    {
        $this->seedTrip([
            'slug' => 'kyoto-2026',
            'title' => 'Kyoto in 4 days',
            'tagline' => 'Sample plan · autumn leaves',
            'subhead' => 'Kyoto at the start of the autumn-leaf peak: Kiyomizu-dera lit up at night, Fushimi Inari before the crowds, Arashiyama at opening time and the Philosopher\'s Path on the way out. Every slot has a cheaper and a fancier option — tap to make it your group\'s.',
            'destination' => 'Kyoto, Japan',
            'origin_label' => 'MNL <-> KIX',
            'start_date' => '2026-11-25',
            'end_date' => '2026-11-28',
            'party_size' => 4,
            'currency' => 'JPY',
            'lat' => 34.9858,   // Kyoto Station
            'lon' => 135.7588,
            'forecast_note' => 'Late November in Kyoto is crisp: ~15 °C by day, ~6 °C at dawn — cold for anyone flying from Manila, so pack a real jacket. Leaf colour moves fast and varies by year: yellows usually peak first, maples turn red into early December. Live numbers fill in inside the ~16-day forecast window.',
            'segments' => [
                [
                    'from' => 'MNL', 'to' => 'KIX', 'date' => 'WED 25 NOV 2026',
                    'depart' => '08:40', 'arrive' => '13:40', 'terminal' => 'NAIA -> KIX T1',
                    'airline' => 'Philippine Airlines', 'flight_no' => '',
                ],
                [
                    'from' => 'KIX', 'to' => 'MNL', 'date' => 'SAT 28 NOV 2026',
                    'depart' => '18:30', 'arrive' => '21:45', 'terminal' => 'KIX T1 -> NAIA',
                    'airline' => 'Philippine Airlines', 'flight_no' => '',
                ],
            ],
            'stats' => [
                ['value' => '4', 'label' => 'days, 3 nights by Kyoto Station'],
                ['value' => '~75 min', 'label' => 'airport train to Kyoto'],
                ['value' => '¥500', 'label' => 'most temple entries'],
                ['value' => '~15°', 'label' => 'daytime high — bring a jacket'],
            ],
        ], $this->days(), $this->budgetLines());
    }

    private function days(): array
    {
        return [
            // ================= DAY 1 =================
            [
                'day_number' => 1,
                'date' => '2026-11-25',
                'title' => 'Land, then Kiyomizu-dera by night',
                'title_secondary' => '水曜日',
                'summary' => 'Airport train to Kyoto, drop the bags, and spend the first evening on the lantern-lit lanes of Higashiyama — the temple\'s autumn night viewing only runs for ten days a year.',
                'weather_tag' => 'outdoor',
                'forecast_date' => '2026-11-25',
                'temp_high' => 15,
                'temp_low' => 6,
                'lat' => 34.9949,
                'lon' => 135.7850,
                'weather_note' => 'Cool and dry most years. It drops fast after sunset (~16:50) — wear the jacket, not just carry it.',
                'outfit_chips' => ['warm jacket', 'sweater or fleece', 'long pants', 'comfortable walking shoes', 'scarf for the evening'],
                'area_label' => 'Base: by Kyoto Station · evening in Higashiyama',
                'map_embed_url' => $this->osm(34.9949, 135.7850, 0.025),
                'hiccups' => [
                    'Japan needs a visa for a Philippine passport — apply through an accredited agency several weeks before you fly.',
                    'The ICOCA & HARUKA combo ticket ended in 2023: buy the HARUKA ticket at the JR office and an IC card (ICOCA) separately for buses and subways.',
                    'Kiyomizu-dera\'s night viewing runs 21–30 November until 21:30 (last entry 21:00) — the lanes below it are packed, so go straight up.',
                    'The souvenir shops on Ninenzaka and Sannenzaka mostly close around 18:00 — shop on the way up, not after.',
                    'In Gion, the side lanes marked "private road" ban photos and entry — fines are ¥10,000. Never photograph or follow geiko or maiko; Hanamikoji\'s main street is public.',
                ],
                'stops' => [
                    [
                        'time' => '13:40', 'title' => 'Land at Kansai Airport (KIX)', 'cost_label' => '~¥2,000 IC card',
                        'description' => 'Immigration, bags, then the JR ticket office for the HARUKA and an ICOCA card (¥500 deposit plus credit) for everything after.',
                    ],
                    [
                        'time' => '15:00', 'title' => 'Airport train to Kyoto', 'cost_label' => '~¥1,200–3,600',
                        'option_label' => 'How to get to Kyoto',
                        'options' => [
                            $this->opt('HARUKA limited express', 'mid', '~75 min, direct · ¥3,060 unreserved', 'One train, luggage racks, lands you at Kyoto Station next to the hotel.', 3060, 3590, true),
                            $this->opt('JR Kansai Airport rapid + change at Osaka', 'budget', '~1h45 · ~¥1,900', 'Cheaper, slower, and a transfer with suitcases at a very busy station.', 1900, 1900),
                            $this->opt('Airport Limousine Bus', 'mid', '~90 min (traffic) · ~¥2,800', 'Bags under the bus and a guaranteed seat — handy for a group with big luggage.', 2800, 2800),
                        ],
                    ],
                    [
                        'time' => '16:30', 'title' => 'Check in near Kyoto Station',
                        'description' => 'The station is the hub: HARUKA, the JR line to Fushimi Inari and Arashiyama, the subway and most buses all start here.',
                        'weather_tag' => 'indoor',
                    ],
                    [
                        'time' => '17:30', 'title' => 'Up Ninenzaka and Sannenzaka', 'cost_label' => '~¥230 bus or taxi',
                        'description' => 'Bus 206 or a taxi split four ways to Gojō-zaka, then the preserved stone lanes up to the temple — matcha soft-serve and pickles on the way.',
                    ],
                    [
                        'time' => '18:15', 'title' => 'Kiyomizu-dera autumn night viewing', 'cost_label' => '~¥500',
                        'description' => 'The wooden stage over a valley of lit-up maples, and a blue searchlight beam over the city. Allow an hour.',
                    ],
                    [
                        'time' => '19:45', 'title' => 'Dinner in Gion', 'cost_label' => '~¥1,000–6,000 food',
                        'option_label' => 'Dinner',
                        'options' => [
                            $this->opt('Ramen or udon near Gion-Shijō', 'budget', 'warm, fast, cheap', 'The right first-night meal after a travel day in the cold.', 1000, 1500, true),
                            $this->opt('Obanzai (Kyoto home cooking) set', 'mid', 'small shared plates', 'Seasonal vegetables, tofu and fish — the everyday food of Kyoto.', 2500, 4000),
                            $this->opt('Kaiseki-style dinner in Gion', 'splurge', 'book ahead', 'A multi-course seasonal meal — the classic Kyoto treat.', 6000, 12000),
                        ],
                    ],
                ],
            ],

            // ================= DAY 2 =================
            [
                'day_number' => 2,
                'date' => '2026-11-26',
                'title' => 'Fushimi Inari, Tōfuku-ji, Nishiki',
                'title_secondary' => '木曜日',
                'summary' => 'The torii-gate mountain at 7 am while it\'s empty, Kyoto\'s most famous maple valley next door, then the city\'s food market and a lantern-lit evening alley.',
                'weather_tag' => 'outdoor',
                'forecast_date' => '2026-11-26',
                'temp_high' => 15,
                'temp_low' => 6,
                'lat' => 34.9671,
                'lon' => 135.7727,
                'weather_note' => 'Cold at 7 am on the mountain — layers you can peel off on the climb.',
                'outfit_chips' => ['layers', 'grippy shoes for stone steps', 'gloves for the early start'],
                'area_label' => 'Fushimi Inari → Tōfuku-ji → downtown',
                'map_embed_url' => $this->osm(34.9800, 135.7700, 0.035),
                'hiccups' => [
                    'Fushimi Inari is open 24 hours and free — by 9:30 the first gates are shoulder to shoulder, so the 7:00 start is the whole point.',
                    'Tōfuku-ji in leaf season is crowded and photos are banned on the Tsūtenkyō bridge — keep walking and shoot from the side halls.',
                    'Many Nishiki Market shops close on Wednesdays and most close between 17:00 and 18:00 — Thursday midday is ideal. Don\'t eat while walking; stand by the stall.',
                    'Rain swap: the Kyoto Railway Museum or the Kyoto National Museum instead of the mountain climb.',
                ],
                'stops' => [
                    [
                        'time' => '07:00', 'title' => 'Fushimi Inari Taisha', 'cost_label' => 'free',
                        'description' => 'JR Nara line two stops from Kyoto Station (~5 min). Thousands of vermilion torii up the mountain.',
                        'option_label' => 'How far up',
                        'options' => [
                            $this->opt('To the Yotsutsuji viewpoint', 'mid', '~1 hr up and back', 'Halfway up, a view over Kyoto and far fewer people. The sweet spot.', 0, 0, true),
                            $this->opt('Senbon Torii only', 'budget', '~30 min', 'The famous double row of gates near the bottom — enough if knees or time are short.', 0, 0),
                            $this->opt('The full summit loop', 'splurge', '2–3 hrs', 'Every gate to the top. Only if the group is fit and the day is cool.', 0, 0),
                        ],
                    ],
                    [
                        'time' => '09:30', 'title' => 'Breakfast by the shrine', 'cost_label' => '~¥500–1,200',
                        'description' => 'Inari-zushi (sweet tofu-pocket sushi, named after the shrine) and a hot drink from the stalls by the station.',
                    ],
                    [
                        'time' => '10:15', 'title' => 'Tōfuku-ji', 'cost_label' => 'ticketed',
                        'description' => 'One stop back on the JR line. A valley of maples under the Tsūtenkyō bridge — one of Kyoto\'s top leaf spots, and busy for it. The Zen rock gardens of the Hōjō are quieter.',
                    ],
                    [
                        'time' => '12:30', 'title' => 'Nishiki Market lunch crawl', 'cost_label' => '~¥1,500–3,000 food',
                        'description' => '"Kyoto\'s kitchen" — a covered 400-metre lane of 100+ stalls. Tamagoyaki on a stick, grilled mochi, tofu doughnuts, pickles to bring home.',
                        'weather_tag' => 'covered',
                    ],
                    [
                        'time' => '14:30', 'title' => 'Afternoon downtown', 'cost_label' => 'free–¥3,000',
                        'option_label' => 'Pick one',
                        'options' => [
                            $this->opt('Teramachi + Shinkyōgoku arcades', 'budget', 'covered shopping streets', 'Souvenirs, stationery and snacks under a roof — the easy afternoon.', 0, 3000, true),
                            $this->opt('Nijō Castle', 'mid', 'the "nightingale floors"', 'The shogun\'s palace whose floors squeak on purpose to expose intruders.', 1300, 1300),
                            $this->opt('Kimono rental + photo walk', 'splurge', 'book ahead', 'A couple of hours in a kimono around Higashiyama. Book a shop with a dressing service.', 4000, 8000),
                        ],
                    ],
                    [
                        'time' => '18:30', 'title' => 'Pontochō for dinner', 'cost_label' => '~¥1,500–8,000 food',
                        'description' => 'The narrow lantern-lit alley along the Kamo River. Yakitori, okonomiyaki or a riverside set. Many places show menus outside — check before you sit.',
                    ],
                ],
            ],

            // ================= DAY 3 =================
            [
                'day_number' => 3,
                'date' => '2026-11-27',
                'title' => 'Arashiyama early, the Golden Pavilion after lunch',
                'title_secondary' => '金曜日',
                'summary' => 'The bamboo grove before 8:30, a Zen garden and a hilltop villa with matcha, then the Golden Pavilion in the afternoon light.',
                'weather_tag' => 'outdoor',
                'forecast_date' => '2026-11-27',
                'temp_high' => 14,
                'temp_low' => 5,
                'lat' => 35.0170,
                'lon' => 135.6713,
                'weather_note' => 'Arashiyama sits by the river in the hills — a couple of degrees colder than downtown in the morning.',
                'outfit_chips' => ['warm layers', 'walking shoes', 'small backpack', 'gloves'],
                'area_label' => 'Arashiyama → Kinkaku-ji',
                'map_embed_url' => $this->osm(35.0170, 135.6713, 0.03),
                'hiccups' => [
                    'The bamboo grove is free and always open, but from ~9:30 it moves at a shuffle — be there by 8:00.',
                    'City buses 101 and 205 to Kinkaku-ji are packed in leaf season, and the bus 1-day pass isn\'t accepted on those routes at peak hours — tap your IC card (¥230) or share a taxi from Arashiyama.',
                    'Kinkaku-ji closes at 17:00 — arrive by 15:30.',
                    'Rain swap: Arashiyama\'s Tenryū-ji halls are covered; swap the boat or train for an indoor hour at the Kyoto International Manga Museum.',
                ],
                'stops' => [
                    [
                        'time' => '07:30', 'title' => 'JR Sagano line to Saga-Arashiyama', 'cost_label' => '~¥240',
                        'description' => '~15 min from Kyoto Station. Walk to the north end of the bamboo grove first.',
                    ],
                    [
                        'time' => '08:00', 'title' => 'Bamboo grove', 'cost_label' => 'free',
                        'description' => 'The towering green corridor, at its quietest before the tour buses.',
                    ],
                    [
                        'time' => '08:30', 'title' => 'Tenryū-ji garden', 'cost_label' => '¥500',
                        'description' => 'A UNESCO-listed Zen temple with a 700-year-old pond garden. The garden opens at 8:30 — and its north gate opens straight onto the bamboo grove.',
                    ],
                    [
                        'time' => '09:45', 'title' => 'The rest of the morning', 'cost_label' => '¥880–1,000+',
                        'option_label' => 'Pick one',
                        'options' => [
                            $this->opt('Ōkōchi Sansō villa', 'mid', '¥1,000 with matcha and a sweet', 'A silent-film star\'s hillside villa and gardens at the top of the bamboo grove — the matcha is included.', 1000, 1000, true),
                            $this->opt('Sagano Romantic Train', 'mid', '¥880 · ~25 min · seats sell out in leaf season', 'An open-sided scenic train up the river gorge. Reserve ahead and ride one way.', 880, 880),
                            $this->opt('Walk the river + Togetsukyō bridge', 'budget', 'free', 'The famous bridge and the mountainside of red and gold behind it.', 0, 0),
                        ],
                    ],
                    [
                        'time' => '12:00', 'title' => 'Lunch in Arashiyama', 'cost_label' => '~¥1,200–4,000 food',
                        'description' => 'Yudōfu (simmered tofu) is the local speciality — or soba from a shop by the station if the group is hungry and cold.',
                    ],
                    [
                        'time' => '14:00', 'title' => 'Kinkaku-ji — the Golden Pavilion', 'cost_label' => '¥500',
                        'description' => 'Gold leaf over a mirror pond. One-way path, ~45 minutes. Late afternoon light is the best for photos.',
                    ],
                    [
                        'time' => '17:30', 'title' => 'Evening at Kyoto Station', 'cost_label' => '~¥1,500–5,000',
                        'description' => 'Ramen Street on the 10th floor, the Skyway glass walkway, and Isetan\'s food hall in the basement for pasalubong — boxed yatsuhashi and matcha sweets.',
                        'weather_tag' => 'indoor',
                    ],
                ],
            ],

            // ================= DAY 4 =================
            [
                'day_number' => 4,
                'date' => '2026-11-28',
                'title' => 'Philosopher\'s Path, then home',
                'title_secondary' => '土曜日',
                'summary' => 'Check out, leave the bags, walk the canal path from the Silver Pavilion to Nanzen-ji\'s red-brick aqueduct, and catch the HARUKA by 14:30.',
                'weather_tag' => 'outdoor',
                'forecast_date' => '2026-11-28',
                'temp_high' => 15,
                'temp_low' => 6,
                'lat' => 35.0244,
                'lon' => 135.7943,
                'weather_note' => 'A gentle walking morning. Saturdays are busier everywhere — start early.',
                'outfit_chips' => ['comfy travel outfit', 'jacket', 'walking shoes'],
                'area_label' => 'Ginkaku-ji → Philosopher\'s Path → Nanzen-ji',
                'map_embed_url' => $this->osm(35.0180, 135.7930, 0.025),
                'hiccups' => [
                    'Leave Kyoto Station on a HARUKA by ~14:30 for an 18:30 flight — KIX recommends arriving 2 hours before international departures, and lines are long on weekends.',
                    'Ginkaku-ji opens at 8:30 and Nanzen-ji\'s grounds and aqueduct are free — the paid sub-temples are optional.',
                    'Hotels hold bags after checkout; coin lockers at Kyoto Station fill up by mid-morning on weekends.',
                ],
                'stops' => [
                    [
                        'time' => '08:00', 'title' => 'Checkout — bags held at the hotel',
                        'description' => 'Bus or taxi to Ginkaku-ji (~30 min).',
                    ],
                    [
                        'time' => '08:45', 'title' => 'Ginkaku-ji — the Silver Pavilion', 'cost_label' => '¥500',
                        'description' => 'Never actually silvered — a quieter, mossier counterpart to the Golden Pavilion, with a raked-sand garden.',
                    ],
                    [
                        'time' => '09:45', 'title' => 'Philosopher\'s Path', 'cost_label' => 'free',
                        'description' => '2 km of stone path along a canal lined with maples and small temples. Cafés along the way for a warm drink.',
                    ],
                    [
                        'time' => '11:00', 'title' => 'Nanzen-ji and the aqueduct', 'cost_label' => 'free–¥600',
                        'option_label' => 'How much Nanzen-ji',
                        'options' => [
                            $this->opt('Grounds + the brick aqueduct', 'budget', 'free', 'The Meiji-era aqueduct arches through the temple grounds — Kyoto\'s favourite photo spot.', 0, 0, true),
                            $this->opt('Climb the Sanmon gate', 'mid', 'ticketed', 'Views over the maples from the giant wooden gate\'s balcony.', 600, 600),
                            $this->opt('Yudōfu lunch near the gate', 'splurge', 'the Nanzen-ji speciality', 'Simmered tofu in a temple-garden restaurant — a slow last meal.', 3000, 4500),
                        ],
                    ],
                    [
                        'time' => '12:30', 'title' => 'Back to the hotel for bags', 'cost_label' => '~¥260 subway',
                        'description' => 'Subway from Keage to Kyoto Station (one change).',
                    ],
                    [
                        'time' => '14:30', 'title' => 'HARUKA to Kansai Airport', 'cost_label' => '~¥3,060',
                        'description' => 'Arrive ~15:45, check in, last shopping airside.',
                    ],
                    [
                        'time' => '18:30', 'title' => 'Fly home to Manila',
                        'description' => 'Lands around 21:45. Trip complete.',
                    ],
                ],
            ],
        ];
    }

    private function budgetLines(): array
    {
        return [
            ['category' => 'Transpo', 'label' => 'Round-trip airfare', 'note' => 'MNL ⇄ KIX', 'amount' => 45000],
            ['category' => 'Transpo', 'label' => 'PH travel tax + terminal fee', 'amount' => 5000],
            ['category' => 'Transpo', 'label' => 'HARUKA both ways', 'note' => 'unreserved', 'amount' => 6120],
            ['category' => 'Transpo', 'label' => 'IC card top-ups', 'note' => 'JR, subway, buses', 'amount' => 4000],
            ['category' => 'Accommodation', 'label' => 'Hotel by Kyoto Station', 'note' => 'leaf-season rate, quad ÷ 4 × 3 nights', 'amount' => 30000],
            ['category' => 'Meals', 'label' => 'Per diem', 'note' => '~¥1,500 / meal × 3 × 4 days', 'amount' => 18000, 'per_person' => true],
            ['category' => 'Rail & entry', 'label' => 'Temples and gardens', 'note' => 'Kiyomizu, Tōfuku-ji, Tenryū-ji, Ōkōchi Sansō, Kinkaku-ji, Ginkaku-ji', 'amount' => 5000],
            ['category' => 'Shopping', 'label' => 'Pasalubong', 'note' => 'yatsuhashi, matcha sweets, pickles', 'amount' => 6000],
            ['category' => 'Insurance', 'label' => 'Travel insurance', 'amount' => 2500],
            ['category' => 'Communication', 'label' => 'eSIM', 'note' => '~4 days', 'amount' => 1500],
            ['category' => 'Visa', 'label' => 'Japan visa via accredited agency', 'note' => 'PH passport', 'amount' => 6000],
        ];
    }
}
