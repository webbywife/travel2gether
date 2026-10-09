<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\BuildsSampleTrip;
use Illuminate\Database\Seeder;

/**
 * Public sample: Osaka + a Nara day trip in cherry-blossom season (early Apr
 * 2027; Osaka's 30-year average is first bloom ~27 Mar, full bloom ~4–5 Apr).
 * Checked Oct 2026: rapi:t ~38 min ¥1,430; Kintetsu Namba–Nara rapid ~40 min
 * ¥680; Osaka Castle tower ¥1,200; Nishinomaru Garden small fee, open late in
 * blossom season; Umeda Sky observatory ¥1,500; Tōdai-ji Daibutsuden ¥800
 * (7:30–17:30 Apr–Oct); deer crackers ¥200; USJ from ~¥8,900 and Super
 * Nintendo World needs a timed-entry ticket; Kuromon ~8:00–18:00.
 */
class Osaka2027Seeder extends Seeder
{
    use BuildsSampleTrip;

    public function run(): void
    {
        $this->seedTrip([
            'slug' => 'osaka-2027',
            'title' => 'Osaka + Nara in 4 days',
            'tagline' => 'Sample plan · cherry blossoms',
            'subhead' => 'Osaka at cherry-blossom time: street food in Dōtonbori, the castle park in bloom, sunset from the Floating Garden, and a day with the bowing deer of Nara — or Universal Studios, if that\'s your group. Tap any slot to swap it.',
            'destination' => 'Osaka, Japan',
            'origin_label' => 'MNL <-> KIX',
            'start_date' => '2027-04-01',
            'end_date' => '2027-04-04',
            'party_size' => 4,
            'currency' => 'JPY',
            'lat' => 34.6687,   // Namba
            'lon' => 135.5013,
            'forecast_note' => 'Early April in Osaka: ~18 °C by day and ~9 °C at night, usually dry and bright. Blossoms don\'t follow the calendar exactly — some years peak a week early or late — so check the bloom forecast (tenki.jp) two weeks out. Live numbers fill in inside the ~16-day forecast window.',
            'segments' => [
                [
                    'from' => 'MNL', 'to' => 'KIX', 'date' => 'THU 01 APR 2027',
                    'depart' => '08:40', 'arrive' => '13:40', 'terminal' => 'NAIA -> KIX T1',
                    'airline' => 'Cebu Pacific', 'flight_no' => '',
                ],
                [
                    'from' => 'KIX', 'to' => 'MNL', 'date' => 'SUN 04 APR 2027',
                    'depart' => '18:30', 'arrive' => '21:45', 'terminal' => 'KIX T1 -> NAIA',
                    'airline' => 'Cebu Pacific', 'flight_no' => '',
                ],
            ],
            'stats' => [
                ['value' => '4', 'label' => 'days, 3 nights in Namba'],
                ['value' => '38 min', 'label' => 'airport train to Namba'],
                ['value' => '40 min', 'label' => 'to Nara\'s deer park'],
                ['value' => '¥680', 'label' => 'train fare to Nara'],
            ],
        ], $this->days(), $this->budgetLines());
    }

    private function days(): array
    {
        return [
            // ================= DAY 1 =================
            [
                'day_number' => 1,
                'date' => '2027-04-01',
                'title' => 'Land, then eat your way down Dōtonbori',
                'title_secondary' => '木曜日',
                'summary' => 'The fast train from the airport drops you in Namba, where the hotel is. The evening is Osaka\'s favourite pastime: kuidaore — eat until you drop.',
                'weather_tag' => 'outdoor',
                'forecast_date' => '2027-04-01',
                'temp_high' => 18,
                'temp_low' => 9,
                'lat' => 34.6687,
                'lon' => 135.5013,
                'weather_note' => 'Mild days, cool nights — a light jacket after dark.',
                'outfit_chips' => ['light jacket', 'long sleeves', 'jeans', 'walking shoes'],
                'area_label' => 'Base: Namba — Dōtonbori is the doorstep',
                'map_embed_url' => $this->osm(34.6687, 135.5013, 0.02),
                'hiccups' => [
                    'Japan needs a visa for a Philippine passport — apply through an accredited agency several weeks before you fly.',
                    'Cherry-blossom week is peak season: book hotels and the rapi:t back to the airport early, and expect queues at every famous food stall.',
                    'Takoyaki comes off the griddle molten — wait a minute before the first bite.',
                    'Dōtonbori is busiest 19:00–21:00 on weekends; Thursday evening is calmer.',
                ],
                'stops' => [
                    [
                        'time' => '13:40', 'title' => 'Land at Kansai Airport (KIX)', 'cost_label' => '~¥2,000 IC card',
                        'description' => 'Immigration and bags, then pick up an ICOCA card for trains and the metro.',
                    ],
                    [
                        'time' => '14:45', 'title' => 'Train to Namba', 'cost_label' => '~¥970–1,430',
                        'option_label' => 'How to get to Namba',
                        'options' => [
                            $this->opt('Nankai rapi:t limited express', 'mid', '~38 min · ¥1,430 · reserved seats', 'The blue "Iron Man" train, straight to Namba with luggage space.', 1430, 1430, true),
                            $this->opt('Nankai airport express', 'budget', '~45 min · ~¥970', 'Same line, regular commuter train — cheaper, fine if you\'re travelling light.', 970, 970),
                            $this->opt('Taxi, split four ways', 'splurge', '~50 min · fixed-fare taxis available', 'Door to door if someone in the group needs it.', 4000, 5000),
                        ],
                    ],
                    [
                        'time' => '16:00', 'title' => 'Check in — Namba',
                        'description' => 'Namba has three train lines, Dōtonbori, Kuromon Market and the Shinsaibashi arcades within walking distance — and the train to Nara.',
                        'weather_tag' => 'indoor',
                    ],
                    [
                        'time' => '17:30', 'title' => 'Hōzenji Yokochō', 'cost_label' => 'free',
                        'description' => 'A lantern-lit stone alley around a tiny temple whose Fudō statue is covered in moss — visitors splash it with water for luck.',
                    ],
                    [
                        'time' => '18:15', 'title' => 'Dōtonbori food crawl', 'cost_label' => '~¥2,000–4,000 food',
                        'description' => 'The canal, the neon, the Glico running man on Ebisu bridge. Share as you go.',
                        'option_label' => 'Main event',
                        'options' => [
                            $this->opt('Takoyaki + okonomiyaki crawl', 'budget', 'share 3–4 stalls', 'Octopus balls from a street stall, then a sit-down okonomiyaki on the griddle.', 1500, 2500, true),
                            $this->opt('Kushikatsu set', 'mid', 'deep-fried skewers', 'One rule: never double-dip in the shared sauce.', 2500, 3500),
                            $this->opt('Kani (crab) restaurant', 'splurge', 'under the giant moving crab sign', 'The full crab course — Dōtonbori\'s famous splurge.', 6000, 10000),
                        ],
                    ],
                    [
                        'time' => '20:30', 'title' => 'Tombori River Walk + Shinsaibashi', 'cost_label' => 'free',
                        'description' => 'The canal-side boardwalk, then the covered Shinsaibashi-suji arcade, open late.',
                        'weather_tag' => 'covered',
                    ],
                ],
            ],

            // ================= DAY 2 =================
            [
                'day_number' => 2,
                'date' => '2027-04-02',
                'title' => 'Castle in bloom, Shinsekai, the Floating Garden',
                'title_secondary' => '金曜日',
                'summary' => 'Thousands of cherry trees around Osaka Castle in the morning, retro Shinsekai for kushikatsu, and the city lights from 173 metres up at sunset.',
                'weather_tag' => 'outdoor',
                'forecast_date' => '2027-04-02',
                'temp_high' => 18,
                'temp_low' => 9,
                'lat' => 34.6873,
                'lon' => 135.5262,
                'weather_note' => 'Picnic weather if it\'s dry. Hanami spots get packed at lunchtime and on weekends.',
                'outfit_chips' => ['layers', 'picnic mat', 'walking shoes', 'sunglasses'],
                'area_label' => 'Osaka Castle → Shinsekai → Umeda',
                'map_embed_url' => $this->osm(34.6800, 135.5100, 0.04),
                'hiccups' => [
                    'Osaka Castle\'s main tower is ¥1,200 and has a lift only part of the way — the park and the blossoms around it are free.',
                    'Nishinomaru Garden (small fee) has the densest blossoms and opens into the evening in sakura season.',
                    'Umeda Sky\'s observatory (¥1,500) gets a queue for the escalator tube at sunset — arrive 30 minutes before.',
                    'Rain swap: the Osaka Museum of History next to the castle, then Shinsekai\'s covered arcades.',
                ],
                'stops' => [
                    [
                        'time' => '09:00', 'title' => 'Osaka Castle Park', 'cost_label' => 'free–¥1,400',
                        'option_label' => 'How to do the castle',
                        'options' => [
                            $this->opt('Nishinomaru Garden + walk the moats', 'budget', 'small garden fee', 'The best blossom view of the white-and-gold keep, then the stone walls and moats.', 200, 300, true),
                            $this->opt('Main tower museum + garden', 'mid', '¥1,200 tower', 'Inside the keep: the history of Hideyoshi and an 8th-floor lookout.', 1400, 1500),
                            $this->opt('Gozabune boat around the moat', 'splurge', '~20 min · ticketed', 'A small boat under the blossoms along the inner moat — book on arrival, it sells out on blossom days.', 1500, 1500),
                        ],
                    ],
                    [
                        'time' => '12:00', 'title' => 'Hanami picnic lunch', 'cost_label' => '~¥800–1,500 food',
                        'description' => 'Convenience-store onigiri, karaage and strawberry sandwiches under the trees — this is what locals do in blossom season.',
                    ],
                    [
                        'time' => '14:00', 'title' => 'Shinsekai + Tsūtenkaku', 'cost_label' => 'free–¥1,200',
                        'description' => 'Osaka\'s retro 1910s neighbourhood of neon, pachinko and kushikatsu shops under the Tsūtenkaku tower. Climb the tower for the lucky Billiken statue, or just walk Janjan Yokochō.',
                        'weather_tag' => 'covered',
                    ],
                    [
                        'time' => '17:30', 'title' => 'Umeda Sky Building — Floating Garden', 'cost_label' => '¥1,500',
                        'description' => 'The open-air rooftop ring between two towers. Stay through sunset (~18:20) as the city lights come on.',
                    ],
                    [
                        'time' => '19:30', 'title' => 'Dinner in Umeda', 'cost_label' => '~¥1,500–5,000 food',
                        'description' => 'Under the tracks at Umeda there are izakaya alleys; the Hankyu and Daimaru food floors for anything else.',
                    ],
                ],
            ],

            // ================= DAY 3 =================
            [
                'day_number' => 3,
                'date' => '2027-04-03',
                'title' => 'Nara — the deer and the Great Buddha',
                'title_secondary' => '土曜日',
                'summary' => 'Forty minutes from Namba: the bowing deer of Nara Park, the giant bronze Buddha in the world\'s largest wooden hall, and a forest shrine of 3,000 lanterns.',
                'weather_tag' => 'outdoor',
                'forecast_date' => '2027-04-03',
                'temp_high' => 18,
                'temp_low' => 7,
                'lat' => 34.6851,
                'lon' => 135.8431,
                'weather_note' => 'Nara\'s park is big and open — sun hat and water. A degree or two cooler in the morning than Osaka.',
                'outfit_chips' => ['comfortable shoes', 'cap', 'bag that zips (the deer eat paper)'],
                'area_label' => 'Nara Park — Tōdai-ji, Kasuga Taisha',
                'map_embed_url' => $this->osm(34.6851, 135.8431, 0.025),
                'hiccups' => [
                    'Deer crackers are ¥200 a bundle — bow, and they bow back. Hide maps and paper bags: they will eat them, and they nip when the crackers run out.',
                    'Tōdai-ji\'s Great Buddha Hall is ¥800, cash only at the gate, open from 7:30 in April — the hall is quietest before 9:30.',
                    'Saturdays in blossom season are Nara\'s busiest day; take the train before 8:00.',
                    'Choosing Universal Studios instead? The Super Nintendo World area needs a separate timed-entry ticket on top of the park ticket, and spring-break Saturdays sell out — buy both well ahead.',
                ],
                'stops' => [
                    [
                        'time' => '07:45', 'title' => 'Your day', 'cost_label' => '¥680–15,000+',
                        'option_label' => 'Nara or the theme park',
                        'options' => [
                            $this->opt('Nara day trip', 'budget', 'Kintetsu rapid from Osaka-Namba · ~40 min · ¥680', 'The plan below. Kintetsu-Nara station is a 10-minute walk from the park.', 1360, 1360, true),
                            $this->opt('Universal Studios Japan instead', 'splurge', 'from ~¥8,900 · Super Nintendo World needs a timed-entry ticket', 'The full day at USJ — buy the park ticket, the Nintendo timed entry (or an Express Pass) online before you fly.', 9000, 25000),
                            $this->opt('Nara by limited express', 'mid', '~40 min · ¥1,410 reserved', 'Same trip in a reserved seat — worth it on a crowded Saturday.', 2820, 2820),
                        ],
                    ],
                    [
                        'time' => '08:30', 'title' => 'Nara Park and the deer', 'cost_label' => '¥200 crackers',
                        'description' => 'Around 1,000+ free-roaming deer, seen as messengers of the gods. Buy crackers from the licensed stalls only.',
                    ],
                    [
                        'time' => '09:15', 'title' => 'Tōdai-ji — the Great Buddha', 'cost_label' => '¥800',
                        'description' => 'A 15-metre bronze Buddha in one of the largest wooden buildings in the world. Squeeze through the hole in a pillar the size of the Buddha\'s nostril for good luck (there\'s a queue).',
                        'weather_tag' => 'covered',
                    ],
                    [
                        'time' => '11:00', 'title' => 'Kasuga Taisha', 'cost_label' => 'free outer grounds',
                        'description' => 'Through the forest to the vermilion shrine famous for its 3,000 stone and bronze lanterns. The inner cloister is a small extra fee.',
                    ],
                    [
                        'time' => '12:30', 'title' => 'Lunch in Naramachi', 'cost_label' => '~¥1,200–3,000 food',
                        'option_label' => 'Lunch',
                        'options' => [
                            $this->opt('Kakinoha-zushi', 'budget', 'sushi wrapped in persimmon leaves', 'Nara\'s local speciality — easy to share.', 1200, 1800, true),
                            $this->opt('Mochi at Nakatanidou', 'budget', 'the high-speed mochi pounding', 'Fresh yomogi mochi, pounded at lightning speed in front of the shop.', 300, 500),
                            $this->opt('Kamameshi set', 'mid', 'rice cooked in an iron pot', 'A proper sit-down meal after the morning on foot.', 1800, 3000),
                        ],
                    ],
                    [
                        'time' => '14:30', 'title' => 'Back to Osaka — Kuromon or a rest', 'cost_label' => '¥680',
                        'description' => 'Kintetsu back to Namba. Rest, or browse Den Den Town (anime, figures, retro games) before dinner.',
                    ],
                    [
                        'time' => '18:30', 'title' => 'Last big dinner', 'cost_label' => '~¥2,000–8,000 food',
                        'description' => 'Yakiniku, a conveyor-belt sushi, or back to Dōtonbori for whatever you missed on Day 1.',
                    ],
                ],
            ],

            // ================= DAY 4 =================
            [
                'day_number' => 4,
                'date' => '2027-04-04',
                'title' => 'Kuromon breakfast, last shopping, home',
                'title_secondary' => '日曜日',
                'summary' => 'Breakfast at "Osaka\'s kitchen", the pasalubong run on Shinsaibashi-suji, and the rapi:t to KIX by 15:00.',
                'weather_tag' => 'covered',
                'forecast_date' => '2027-04-04',
                'temp_high' => 18,
                'temp_low' => 9,
                'lat' => 34.6654,
                'lon' => 135.5065,
                'weather_note' => 'Mostly covered markets and arcades — a good day if it rains.',
                'outfit_chips' => ['comfy travel outfit', 'light jacket', 'sneakers'],
                'area_label' => 'Kuromon → Shinsaibashi → KIX',
                'map_embed_url' => $this->osm(34.6654, 135.5065, 0.02),
                'hiccups' => [
                    'Kuromon Market runs roughly 8:00–18:00, but many stalls wind down by mid-afternoon, and prices are aimed at tourists — check before you order.',
                    'Tax-free shopping needs your passport and a minimum spend per shop, per day.',
                    'Take the rapi:t by ~15:00 for an 18:30 flight; Sunday afternoons at KIX check-in are busy.',
                ],
                'stops' => [
                    [
                        'time' => '08:00', 'title' => 'Checkout — bags held at the hotel',
                    ],
                    [
                        'time' => '08:30', 'title' => 'Kuromon Market breakfast', 'cost_label' => '~¥1,500–5,000 food',
                        'option_label' => 'What to eat',
                        'options' => [
                            $this->opt('Graze the stalls', 'budget', 'tamagoyaki, grilled scallops, strawberries', 'Small plates from several stalls, eaten at the stall\'s counter.', 1500, 2500, true),
                            $this->opt('Sashimi or a sushi breakfast', 'mid', 'fresh-cut at the fish stalls', 'Tuna and salmon cut in front of you.', 2500, 4000),
                            $this->opt('Wagyu skewers + uni', 'splurge', 'the market\'s showpieces', 'Pricey, but a once-a-trip treat.', 4000, 7000),
                        ],
                    ],
                    [
                        'time' => '10:30', 'title' => 'Pasalubong run', 'cost_label' => '~¥5,000–20,000',
                        'option_label' => 'Where to shop',
                        'options' => [
                            $this->opt('Don Quijote Dōtonbori', 'budget', 'open 24 hours · tax-free counter', 'KitKats, snacks, cosmetics and medicines in one sweep.', 5000, 10000, true),
                            $this->opt('Shinsaibashi-suji arcade', 'mid', '600 m covered street', 'Drugstores, Uniqlo, GU, character shops — the whole list under one roof.', 5000, 15000),
                            $this->opt('Namba station food floors', 'mid', 'boxed sweets built to travel', 'Osaka-only gift boxes, cheesecake and senbei.', 3000, 8000),
                        ],
                    ],
                    [
                        'time' => '14:00', 'title' => 'Collect bags → rapi:t to KIX', 'cost_label' => '¥1,430',
                        'description' => 'Board ~15:00, arrive ~15:40.',
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
            ['category' => 'Transpo', 'label' => 'Round-trip airfare', 'note' => 'MNL ⇄ KIX, sakura season', 'amount' => 45000],
            ['category' => 'Transpo', 'label' => 'PH travel tax + terminal fee', 'amount' => 5000],
            ['category' => 'Transpo', 'label' => 'rapi:t both ways', 'amount' => 2860],
            ['category' => 'Transpo', 'label' => 'IC card — metro + Nara train', 'amount' => 4000],
            ['category' => 'Accommodation', 'label' => 'Hotel in Namba', 'note' => 'sakura-season rate, quad ÷ 4 × 3 nights', 'amount' => 27000],
            ['category' => 'Meals', 'label' => 'Per diem', 'note' => '~¥1,500 / meal × 3 × 4 days', 'amount' => 18000, 'per_person' => true],
            ['category' => 'Rail & entry', 'label' => 'Castle, Umeda Sky, Tōdai-ji, deer crackers', 'amount' => 4000],
            ['category' => 'Shopping', 'label' => 'Pasalubong', 'note' => 'Don Quijote, Shinsaibashi', 'amount' => 10000],
            ['category' => 'Insurance', 'label' => 'Travel insurance', 'amount' => 2500],
            ['category' => 'Communication', 'label' => 'eSIM', 'note' => '~4 days', 'amount' => 1500],
            ['category' => 'Visa', 'label' => 'Japan visa via accredited agency', 'note' => 'PH passport', 'amount' => 6000],
        ];
    }
}
