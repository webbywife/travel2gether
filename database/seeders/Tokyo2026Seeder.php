<?php

namespace Database\Seeders;

use App\Models\Trip;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * The hand-built Tokyo 2026 itinerary (webbywife.github.io/tokyo2026), ported
 * into the Travel2gether schema as a second public sample. Four day-trips out
 * of a Shinjuku base — Okutama, Mito, Kamakura/Enoshima — plus Day 1's arrival.
 */
class Tokyo2026Seeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            Trip::where('slug', 'tokyo-2026')->delete();

            $trip = Trip::create([
                'slug' => 'tokyo-2026',
                'title' => 'Tokyo 2026',
                'tagline' => 'Trip log',
                'subhead' => 'Second time in Tokyo — so this one mostly lives outside it. Okutama\'s limestone caves and river gorge, Mito\'s great garden and the kochia coast, and the Kamakura–Enoshima run, one per day. Central Tokyo is the evenings, the shopping, and a rain plan.',
                'destination' => 'Tokyo, Japan',
                'origin_label' => 'MNL <-> HND',
                'start_date' => '2026-09-09',
                'end_date' => '2026-09-14',
                'party_size' => 2,
                'currency' => 'USD',
                'map_provider' => 'google',
                'lat' => 35.6938,   // Shinjuku
                'lon' => 139.7036,
                'forecast_note' => 'Each day shows live numbers for that day\'s actual destination — not just central Tokyo. Mid-September in the Kantō region is still summer: hot and humid near the coast (~31 °C), cooler up in Hakone and Nikkō, and it\'s the tail of typhoon season, so a passing storm can suspend the Odakyū, Tōbu, or Enoden lines with little warning.',
                'segments' => [
                    [
                        'from' => 'MNL', 'to' => 'HND', 'date' => 'WED 09 SEP 2026',
                        'depart' => '23:30', 'arrive' => '04:45 +1', 'terminal' => 'NAIA 1 -> HND 3',
                        'airline' => 'Philippine Airlines', 'flight_no' => 'PR 420',
                    ],
                    [
                        'from' => 'HND', 'to' => 'MNL', 'date' => 'MON 14 SEP 2026',
                        'depart' => '01:00', 'arrive' => '05:05', 'terminal' => 'HND 3 -> NAIA 1',
                        'airline' => 'Philippine Airlines', 'flight_no' => 'PR 422',
                    ],
                ],
                'stats' => [
                    ['value' => '4', 'label' => 'areas near Tokyo'],
                    ['value' => '3', 'label' => 'nights in Shinjuku'],
                    ['value' => '~2h', 'label' => 'farthest hop each way'],
                    ['value' => '~9h', 'label' => 'total flight time'],
                ],
                'is_public' => true,
            ]);

            foreach ($this->days() as $i => $day) {
                $stops = $day['stops'];
                unset($day['stops']);
                $tripDay = $trip->days()->create($day + ['sort' => $i]);

                foreach ($stops as $j => $stop) {
                    $options = $stop['options'] ?? [];
                    unset($stop['options']);
                    $record = $tripDay->stops()->create($stop + ['sort' => $j, 'has_options' => count($options) > 0]);
                    foreach ($options as $k => $option) {
                        $record->options()->create($option + ['sort' => $k]);
                    }
                }
            }

            foreach ($this->budgetLines() as $i => $line) {
                $trip->budgetLines()->create($line + ['sort' => $i]);
            }
        });
    }

    private function opt(string $name, ?string $tier, ?string $meta, string $note, ?int $min, ?int $max, bool $pick = false): array
    {
        return [
            'name' => $name,
            'tier' => $tier,
            'note' => trim(($meta ? "$meta — " : '') . $note),
            'cost_min' => $min,
            'cost_max' => $max,
            'is_default_pick' => $pick,
        ];
    }

    private function days(): array
    {
        return [
            // ================= DAY 1 =================
            [
                'day_number' => 1,
                'date' => '2026-09-10',
                'title' => 'Land & ease in',
                'title_secondary' => '木曜日',
                'summary' => 'A red-eye leaves you half a day and no stamina, so Day 1 is a menu, not a plan — one short outing within ~45 minutes of the hotel, an obvious dinner attached to it, and a free view or an early night to close.',
                'weather_tag' => 'outdoor',
                'forecast_date' => '2026-09-10',
                'temp_high' => 31,
                'temp_low' => 25,
                'weather_note' => 'Hot, humid — keep it light. You land before dawn on a red-eye, so the morning is a nap and the afternoon is one short, low-effort outing near the hotel. Home by 9 — Okutama is a dawn start.',
                'outfit_chips' => ['breathable tee', 'light layer for train AC', 'walking shoes', 'compact umbrella'],
                'outfit_photos' => [
                    ['url' => 'https://images.pexels.com/photos/934063/pexels-photo-934063.jpeg?auto=compress&cs=tinysrgb&w=600', 'label' => 'Idea 1', 'alt' => 'Striped top, jeans, sunglasses'],
                    ['url' => 'https://images.pexels.com/photos/28645956/pexels-photo-28645956.jpeg?auto=compress&cs=tinysrgb&w=600', 'label' => 'Idea 2', 'alt' => 'Sneakers, sunglasses, tote bag'],
                    ['url' => 'https://images.pexels.com/photos/3944690/pexels-photo-3944690.jpeg?auto=compress&cs=tinysrgb&w=600', 'label' => 'Idea 3', 'alt' => 'Jeans, scarf, sneakers'],
                ],
                'area_label' => 'Base: Shinjuku — outings fan out from here',
                'map_embed_url' => 'https://www.openstreetmap.org/export/embed.html?bbox=139.6700%2C35.6650%2C139.7250%2C35.6980&layer=mapnik&marker=35.6938%2C139.7036',
                'hiccups' => [
                    'If you barely slept, take the Shinjuku-circle option or Thermae-Yu — both are minimal transit and fold up small.',
                    'Shinjuku Gyoen closes mid-to-late afternoon and doesn\'t open until 09:00 — if you want it, breakfast has to stretch and you trade nap time for it.',
                    'teamLab Planets sells out days ahead — book before you fly. Don\'t start Kawagoe later than ~16:00 or the storehouse street will be shutting.',
                    'Chōfu plus a 16:00 outing is two outings on no sleep — if you do Jindai-ji in the morning, treat the evening slot as optional.',
                    'Whatever you pick, be back by ~22:00 — Day 2 leaves on the Chūō line at 07:15 and the Ōme-line connection past Ōme is unforgiving if you miss it.',
                ],
                'stops' => [
                    [
                        'time' => '04:45', 'title' => 'Land at Haneda Terminal 3', 'cost_label' => '~$4–10 transport',
                        'description' => 'Immigration + bags, then buy a Welcome Suica / PASMO Passport at the machines. The first limousine bus leaves T3 at 06:15.',
                        'option_label' => 'Getting to Shinjuku',
                        'options' => [
                            $this->opt('Airport Limousine Bus → Shinjuku', 'mid', 'first bus 06:15 → Shinjuku Stn 06:55, ¥1,400', 'Easiest with luggage after a red-eye — one seat, no stairs. Calls at Shinjuku Station / Keiō Department Store, ~8 min on foot to the hotel.', 9, 9, true),
                            $this->opt('Keikyū + JR Yamanote', 'budget', 'Keikyū to Shinagawa, transfer to Shinjuku · ~40 min · ¥660', 'Cheapest and quick, but two escalator changes with bags.', 4, 5),
                            $this->opt('Taxi, direct', 'splurge', '~30–45 min at that hour · ¥7,000–9,000', 'Door to door in one ride if you\'re past caring about the fare.', 50, 60),
                        ],
                    ],
                    [
                        'time' => '07:15', 'title' => 'Bag drop at Hotel Gracery Shinjuku',
                        'description' => 'Check-in isn\'t until 15:00 — the front desk holds luggage from arrival. Kabukichō 1-19-1, in the Shinjuku Toho Building with the Godzilla head on the roof.',
                    ],
                    [
                        'time' => '07:30', 'title' => 'Breakfast, then sleep', 'cost_label' => '~$3–25 food',
                        'description' => 'Check-in is after 15:00, so there\'s no room to nap in for another seven hours. Eat, then go and sleep somewhere that lets you.',
                        'option_label' => 'Breakfast options',
                        'options' => [
                            $this->opt('Matsuya — the breakfast set', 'budget', '~5-min walk · open 24 hours · ¥400–500', 'A proper hot Japanese breakfast — grilled salmon or nattō, rice, miso soup — for about three dollars, served all night. Order on the machine at the door.', 3, 4, true),
                            $this->opt('Edinburgh (珈琲貴族エジンバラ)', 'mid', '~9-min walk · open 24 hours', 'A proper old-school kissaten — velvet chairs, siphon coffee, thick toast — open around the clock. Smoking allowed.', 6, 9),
                            $this->opt('Conbini haul to the room', 'budget', '7-Eleven / Lawson by the hotel · ¥500–800', 'Onigiri, egg sando, bottled coffee — eat, then straight to sleep.', 4, 6),
                            $this->opt('Sushizanmai — 24-hour sushi', 'splurge', '~4-min walk · open 24 hours', 'Sushi at seven in the morning is an extremely Kabukichō thing to do, and this branch never shuts. The one you\'ll still be telling people about.', 12, 25),
                        ],
                    ],
                    [
                        'time' => '08:30', 'title' => 'Kill the gap until check-in', 'cost_label' => 'free–$22',
                        'description' => 'Seven hours between bag drop and a 15:00 check-in. Bags stay at the front desk. Don\'t try to tough it out on lobby chairs.',
                        'option_label' => 'Pick one',
                        'options' => [
                            $this->opt('Chōfu — Jindai-ji temple & soba', 'mid', 'Keiō line ~20 min + a 15-min bus · leave by 08:00 · ¥560 + ¥500 gardens', 'The gap-filler that\'s actually a destination — a 1,300-year-old temple in a wooded valley, a National Treasure bronze Buddha, and a dozen hand-made jindai-ji soba houses.', 6, 12, true),
                            $this->opt('Thermae-Yu — bathe and actually sleep', 'mid', '~4-min walk · open 24 hours · ¥3,000–3,500', 'The only option where you get real horizontal sleep — a hot-spring complex with reclining-chair rest lounges. Bathe off the flight, then crash until early afternoon.', 22, 24),
                            $this->opt('Shinjuku Gyoen — sit under the trees', 'budget', '~17-min walk · opens 09:00 · ¥500', 'Lawns, huge shade trees, a greenhouse and a tea house. Doesn\'t open until 09:00, and mid-September is hot — take water.', 3, 4),
                            $this->opt('Kaikatsu CLUB — a private booth', 'budget', '~3-min walk · 24 hours · ¥1,500–2,500', 'A private reclining booth, free drinks, showers, and nobody minding if you sleep for five hours. Unglamorous but a much better nap than a park bench.', 10, 17),
                        ],
                    ],
                    [
                        'time' => '15:00', 'title' => 'Check in properly, shower, repack a day bag',
                        'description' => 'Earliest the room is available. Out the door by ~16:00 — pick one outing below and stick to it, no stacking on a red-eye day.',
                    ],
                    [
                        'time' => '16:00', 'title' => 'Ease-in outing', 'cost_label' => 'free–$25',
                        'option_label' => 'Pick one',
                        'options' => [
                            $this->opt('Shinjuku circle: Gyoen → Meiji Jingu → Harajuku at dusk', 'budget', 'zero transit — all within ~2 km of the hotel · ¥500', 'The lowest-effort option, and the safest on no sleep: the garden, the forested shrine at golden hour, then Omotesandō and Takeshita Street after dark. Walk the loop back.', 3, 4, true),
                            $this->opt('Kawagoe — "Little Edo"', 'budget', 'Seibu Shinjuku Line direct, ~45 min · ¥1,000 return', 'An Edo warehouse street, the Toki-no-Kane bell tower, and Kita-in\'s 540 stone rakan. Stays lit into the evening and shrugs off light rain.', 7, 7),
                            $this->opt('Nakano', 'budget', '1 stop from Shinjuku on the Chūō line', 'Nakano Broadway — a covered warren of retro toys, watches, manga and Mandarake — then the lantern-strung yokochō behind the station. Entirely under a roof: the wet-evening pick.', 0, 20),
                            $this->opt('teamLab Planets, Toyosu', 'splurge', '~35 min · pre-booked slot · open late · ¥3,800', 'Barefoot, wade-through digital-art rooms — entirely indoor, runs into the evening. Book the slot before you fly.', 25, 25),
                            $this->opt('Nihon Minka-en, Kawasaki', 'budget', 'Odakyū to Mukōgaoka-Yūen, ~30 min · closes ~16:30 · ¥500', 'Twenty-five thatched farmhouses relocated into a hillside forest — an open-air village of old Japan, almost empty. The "skip the nap and go straight there" option.', 3, 4),
                        ],
                    ],
                    [
                        'time' => '19:00', 'title' => 'Dinner — wherever you ended up', 'cost_label' => '~$9–30 food',
                        'option_label' => 'Dinner options',
                        'options' => [
                            $this->opt('Omoide Yokochō, Shinjuku', 'mid', '~4-min walk, west of Shinjuku Stn', 'If you stayed local — a lantern-lit alley of 6-seat yakitori counters under the tracks. The iconic first Tokyo night, and you can stumble home.', 15, 25, true),
                            $this->opt('Wherever the outing landed you', 'mid', 'Kawagoe / Nakano / Kōenji each have their own alleys', 'Every neighbourhood option has an obvious dinner scene attached — eat there, don\'t trek back to Shinjuku for it.', 10, 25),
                            $this->opt('Ichiran or Fūunji ramen', 'budget', 'near the south / west exits', 'A ramen counter and an early night — no fuss, no wait past 8pm on a weekday.', 9, 13),
                        ],
                    ],
                    [
                        'time' => '20:30', 'title' => 'A free night view, or bed', 'cost_label' => 'free–$12',
                        'option_label' => 'Evening options',
                        'options' => [
                            $this->opt('Tokyo Metropolitan Government Building — free deck', 'budget', '45F, ~202 m, ~14 min from the hotel, open till ~22:00', 'A free night skyline — pick out Skytree, and Fuji\'s silhouette at dusk on a clear evening. There\'s a projection show on the facade too.', 0, 0, true),
                            $this->opt('Golden Gai walk-through', 'budget', '~4-min walk — just behind the hotel', 'Six lanes of tiny themed bars — atmospheric even sober. Some charge a table cover; the tourist-friendly ones say so at the door.', 0, 12),
                            $this->opt('Straight to bed', 'budget', 'you\'ve been up ~24 hours', 'The correct answer more often than not.', 0, 0),
                        ],
                    ],
                    [
                        'time' => '21:00', 'title' => 'Shopping — the electronics sweep', 'cost_label' => 'free to browse',
                        'description' => 'Tonight is for pricing, not buying — work out what you actually want while you\'re fresh-ish, then do the real haul on Day 4.',
                        'option_label' => 'Where to scout',
                        'options' => [
                            $this->opt('Yodobashi Camera, Shinjuku West', 'mid', '~8-min walk · till 22:00', 'The original Yodobashi, spilling across several buildings — cameras in one, audio in another, a genuinely good stationery floor in a third. Ask for the tax-free counter.', 0, null, true),
                            $this->opt('BIC Camera Shinjuku East', 'mid', '~6-min walk from the hotel · till 22:00', 'The same stock as Yodobashi, closer to our side of the station, usually a different point-card promotion. Rice cookers, shavers and beauty devices are the classic wins.', 0, null),
                            $this->opt('Don Quijote Shinjuku East', 'budget', '~3-min walk — same block · open 24 hours', 'Chaos in aisle form, and the single best stop for the bulk take-home haul — regional KitKats, snacks, cosmetics, cheap suitcases. Never closes, so it\'s also the Day 4 fallback.', 0, null),
                        ],
                    ],
                ],
            ],

            // ================= DAY 2 =================
            [
                'day_number' => 2,
                'date' => '2026-09-11',
                'title' => 'Okutama — caves & river gorge',
                'title_secondary' => '金曜日',
                'summary' => 'Okutama is the closest place to Tokyo that actually feels rural and cool — 90 minutes of Chūō-line commuters, then a limestone cave that ignores the weather, a river gorge, and a reservoir lake, with barely a foreign face all day.',
                'weather_tag' => 'covered',
                'forecast_date' => '2026-09-11',
                'temp_high' => 26,
                'temp_low' => 18,
                'weather_note' => 'Cooler up the Tama valley, showers roll through. Okutama runs a few degrees below the city. The limestone cave holds 11 °C whatever the sky\'s doing — bring a layer for inside and grippy shoes for wet rock.',
                'outfit_chips' => ['light layer for the cave', 'grippy shoes for wet rock', 'water + a snack', '~¥5,000 cash', 'rain jacket'],
                'outfit_photos' => [
                    ['url' => 'https://images.pexels.com/photos/934070/pexels-photo-934070.jpeg?auto=compress&cs=tinysrgb&w=600', 'label' => 'Idea 1', 'alt' => 'White sherpa cardigan, jeans'],
                    ['url' => 'https://images.pexels.com/photos/15667095/pexels-photo-15667095.jpeg?auto=compress&cs=tinysrgb&w=600', 'label' => 'Idea 2', 'alt' => 'Cream knit cardigan, jeans'],
                    ['url' => 'https://images.pexels.com/photos/8318230/pexels-photo-8318230.jpeg?auto=compress&cs=tinysrgb&w=600', 'label' => 'Idea 3', 'alt' => 'Comfortable trousers, flats'],
                ],
                'area_label' => 'Okutama — the upper Tama valley',
                'map_embed_url' => 'https://www.openstreetmap.org/export/embed.html?bbox=139.0000%2C35.7750%2C139.2000%2C35.8600&layer=mapnik&marker=35.8090%2C139.0970',
                'hiccups' => [
                    'Ōme-line trains past Ōme run every 30–60 min and the last useful one back is mid-evening — photograph the return timetable at Okutama Station before you head up to the caves.',
                    'The Nippara road is single-lane; the bus fills on a fine weekend and forms a queue — take the first one after you arrive.',
                    'Okutama is cash country — draw money at Shinjuku before you leave; the ATMs up here are few and close early.',
                    'In heavy rain the gorge trail and the dam bridge shut and the river browns — the cave plus a wasabi-soba lunch still make a half-day, then head back early.',
                ],
                'stops' => [
                    [
                        'time' => '07:15', 'title' => 'Coffee, then the Chūō line west', 'cost_label' => '~$5 food · ~¥1,050 rail',
                        'description' => '~2 hours, changing to the Ōme line at Ōme — check for a through "Ōme Special Rapid" that saves the transfer. Draw ~¥5,000 cash at Shinjuku first.',
                    ],
                    [
                        'time' => '09:15', 'title' => 'Arrive Okutama Station',
                        'description' => 'A small timber station at the head of the valley. Photograph the return timetable now. The Nippara bus leaves from the stop out front.',
                    ],
                    [
                        'time' => '09:30', 'title' => 'Bus up to the Nippara Limestone Caves', 'cost_label' => '~$6 + ¥500 bus',
                        'description' => '~30 min up a narrowing gorge road, then a 30–40 min loop through lit limestone chambers — stalactite pillars, a "hundred-tatami" hall, an underground shrine — at a steady 11 °C. The weatherproof core of the day.',
                        'weather_tag' => 'indoor',
                    ],
                    [
                        'time' => '11:30', 'title' => 'Bus back to town, lunch in Okutama', 'cost_label' => '~$8–15 food',
                        'description' => 'Wasabi grown in the cold streams here, and river fish — ayu, yamame — are the local things. A couple of soba houses by the station do both; several places close by 15:00, so don\'t dawdle.',
                    ],
                    [
                        'time' => '12:45', 'title' => 'Pick your afternoon', 'cost_label' => 'free–$8',
                        'option_label' => 'Pick one',
                        'options' => [
                            $this->opt('Hatonosu Gorge riverside trail', 'budget', 'one stop back toward Ōme, Hatonosu Station', 'A ~40-min path along turquoise pools of the Tama River, over the swaying Un-un suspension bridge, past a 200-year-old cedar to a riverside café. Best light is early afternoon.', 0, 0, true),
                            $this->opt('Lake Okutama + Ogōchi Dam', 'budget', 'bus from the station, ~15 min', 'Walk out across the dam and along the "drum-can floating bridge," Tokyo\'s water supply spread out below wooded ridges. Big, quiet, and level.', 0, 0),
                            $this->opt('Mt Mitake + Musashi-Mitake Shrine', 'mid', 'train to Mitake, bus + cable car · ¥1,140 cable car return', 'A cable car to a mountaintop shrine village with a 350-year-old cedar and the Rock Garden trail past waterfalls. The most walking; needs decent weather.', 8, 8),
                        ],
                    ],
                    [
                        'time' => '16:00', 'title' => 'Head back to Shinjuku', 'cost_label' => '~¥1,050 rail',
                        'description' => 'Ōme-line locals are infrequent — go by the timetable you photographed. Arrive Shinjuku ~18:00–18:30.',
                    ],
                    [
                        'time' => '18:30', 'title' => 'Shopping — stationery & clothes', 'cost_label' => '~$20–80',
                        'description' => 'The only window these fit in: stationery and clothing shops shut at 20:00–21:00, unlike the electronics floors. All four are within ten minutes of the station.',
                        'option_label' => 'Where to shop',
                        'options' => [
                            $this->opt('Sekaido, Shinjuku 3-chōme', 'budget', '~7-min walk · closes 20:00 · ~20% off list', 'Five floors of art supplies and stationery at a standing discount — the best-value stationery stop in Tokyo. Not pretty, very deep. Come here before Loft.', 20, 40, true),
                            $this->opt('Loft Shinjuku', 'mid', 'Shinjuku 3-chōme · closes 21:00', 'The gift-shaped version of the same thing — designer stationery, washi tape, stickers, small homeware, already souvenir-wrapped.', 25, 60),
                            $this->opt('Beams Japan, Shinjuku', 'splurge', 'east side of Shinjuku Stn · closes 20:00', 'Six floors of Japanese-made clothing, craft, ceramics and food — the one clothing stop that doubles as a proper souvenir shop.', 60, 150),
                            $this->opt('Uniqlo & GU, Shinjuku-sanchōme', 'budget', '~7-min walk · closes 21:00', 'Japan-only lines at roughly half what they cost at home, plus tax-free over ¥5,000. GU, the cheaper sister brand, is upstairs.', 20, 60),
                        ],
                    ],
                    [
                        'time' => '19:30', 'title' => 'Dinner near the hotel', 'cost_label' => '~$9–25 food',
                        'description' => 'After a mountain day, a Shinjuku izakaya or a ramen counter and an early night — Day 3 is another out-and-back.',
                    ],
                ],
            ],

            // ================= DAY 3 =================
            [
                'day_number' => 3,
                'date' => '2026-09-12',
                'title' => 'Mito gardens + Hitachi Seaside Park',
                'title_secondary' => '土曜日',
                'summary' => 'Two things that are genuinely quiet by Kantō standards — a 180-year-old garden built for the public, and a coast park most foreign visitors never hear of — linked by one limited-express line out of Ueno. The kochia hills alone are worth the ride.',
                'weather_tag' => 'outdoor',
                'forecast_date' => '2026-09-12',
                'temp_high' => 28,
                'temp_low' => 21,
                'weather_note' => 'Open sky, coastal breeze — better dry. Both stops are outdoors: a stroll garden and a big seaside flower park. Little shelter if it rains, so this is the day to move if the forecast turns.',
                'outfit_chips' => ['sun hat + sunscreen', 'breathable layers', 'comfortable walking shoes', 'windbreaker for the coast'],
                'outfit_photos' => [
                    ['url' => 'https://images.pexels.com/photos/17900105/pexels-photo-17900105.jpeg?auto=compress&cs=tinysrgb&w=600', 'label' => 'Idea 1', 'alt' => 'Light layers for a cool day'],
                    ['url' => 'https://images.pexels.com/photos/7836547/pexels-photo-7836547.jpeg?auto=compress&cs=tinysrgb&w=600', 'label' => 'Idea 2', 'alt' => 'Light sweater, comfortable shoes'],
                    ['url' => 'https://images.pexels.com/photos/3030690/pexels-photo-3030690.jpeg?auto=compress&cs=tinysrgb&w=600', 'label' => 'Idea 3', 'alt' => 'Layers and walking shoes'],
                ],
                'area_label' => 'Mito & the Ibaraki coast',
                'map_embed_url' => 'https://www.openstreetmap.org/export/embed.html?bbox=140.4200%2C36.3300%2C140.6300%2C36.4200&layer=mapnik&marker=36.3800%2C140.4560',
                'hiccups' => [
                    'It\'s an open, exposed day — a wet forecast is the cue to swap it. The Kōdōkan, the Mito museums, and Fukuroda Falls (mostly covered walkways) are the rainy-day fallbacks in the same direction.',
                    'Hitachi Seaside Park closes at 17:00 and the last park bus to Katsuta is soon after — watch the clock on the bike.',
                    'Reserved limited-express seats to Mito sell out on a Saturday — book both legs in the morning.',
                    'The kochia are green-to-blushing in mid-September, not full scarlet (that\'s October) — go for the shapes and the sea, not peak colour.',
                ],
                'stops' => [
                    [
                        'time' => '07:00', 'title' => 'Coffee, then the limited express from Ueno', 'cost_label' => '~$5 food · ~¥2,000 one-way',
                        'description' => 'The Hitachi / Tokiwa limited express to Mito, ~75 min, reserved seats. A JR East Pass covers it if you hold one.',
                    ],
                    [
                        'time' => '08:30', 'title' => 'Kairakuen — one of the three great gardens', 'cost_label' => '~$2 + ¥250 villa',
                        'description' => 'Built 1841 and meant, unusually, to be enjoyed by everyone — a 3,000-tree plum grove (bare in September, but the framework is lovely), a cool bamboo and cedar grove, and the three-storey Kōbuntei villa with a view over Senba Lake.',
                    ],
                    [
                        'time' => '10:15', 'title' => 'Second Mito stop', 'cost_label' => 'free–$3',
                        'option_label' => 'Pick one',
                        'options' => [
                            $this->opt('Kōdōkan', 'budget', 'near Mito Station, opposite the castle site · ¥400', 'The largest domain academy of the Edo period — wooden halls where the last shogun studied, and later sheltered. The wet-weather anchor for this day, 10 minutes from the train.', 3, 3, true),
                            $this->opt('Semba Lake loop', 'budget', 'below Kairakuen', 'A flat 3 km path around the lake at the foot of the garden — rental bikes, swans, and the garden\'s cliff of trees reflected in the water.', 0, 0),
                            $this->opt('Straight to the coast', 'budget', 'skip the second stop', 'Bank the time for the Seaside Park and the bike hire.', 0, 0),
                        ],
                    ],
                    [
                        'time' => '11:30', 'title' => 'Train to Katsuta, bus to the park', 'cost_label' => '~¥770 rail + ¥400 bus',
                        'description' => 'Back through Mito to Katsuta (~15 min), then a ~20-min bus to the park gate. Grab lunch in Katsuta Station or at the park\'s food court.',
                    ],
                    [
                        'time' => '12:30', 'title' => 'Hitachi Seaside Park', 'cost_label' => '~$3 (+ ¥600 bike)',
                        'description' => '190 hectares on the Pacific — the famous thing is Miharashi Hill, blanketed in ~32,000 kochia bushes: rounded green mounds in mid-September, turning pink then scarlet through October. Hire a bike for the 11 km of cycle paths.',
                    ],
                    [
                        'time' => '15:30', 'title' => 'One more, or head back', 'cost_label' => 'free–$4',
                        'option_label' => 'Pick one',
                        'options' => [
                            $this->opt('Ōarai Isosaki Shrine — the sea torii', 'budget', 'Ōarai-Kashima line from Mito, ~15 min + walk', 'A torii standing on a wave-cut rock right in the surf — the "Kamiiso" gate, best at high tide or sunset. Far quieter than the famous ones.', 0, 0, true),
                            $this->opt('Nakaminato fish market', 'mid', 'one stop from Katsuta', 'A big, cheap seafood market and food stalls by the harbour — an early dinner of grilled scallops and a rice bowl before the train.', 8, 20),
                            $this->opt('Straight to the express', 'budget', 'from Katsuta or Mito', 'Home for a proper Shinjuku dinner and an earlier night.', 0, 0),
                        ],
                    ],
                    [
                        'time' => '17:00', 'title' => 'Limited express back to Ueno', 'cost_label' => '~¥2,000',
                        'description' => '~75–90 min. Arrive ~18:30–19:00 — it drops you at Ueno, which is where the food shopping below is.',
                    ],
                    [
                        'time' => '18:45', 'title' => 'Shopping — food to bring home', 'cost_label' => '~$20–60',
                        'description' => 'The food-omiyage day, and it costs no extra travel: Ameyoko starts at the south end of the station the express arrives into.',
                        'option_label' => 'Where to shop',
                        'options' => [
                            $this->opt('Ameyoko market, Ueno', 'budget', 'Ueno Stn south end · most stalls shut ~20:00', 'A 500-metre market strip under the Yamanote tracks — dried seafood, nuts, matcha, sweets sold by the kilo at a third of department-store prices. The most efficient way to fill a bag with edible presents.', 20, 40, true),
                            $this->opt('Mito nattō & hoshi-imo', 'budget', 'Mito Station stands, before the express home', 'Ibaraki\'s two keep-forever specialities. Dried nattō is crunchy, savoury, shelf-stable; hoshi-imo is dried sweet potato, and the prefecture grows ~90% of Japan\'s.', 5, 15),
                            $this->opt('Nakaminato dried seafood & senbei', 'budget', 'only if you took the market stop at 15:30', 'Grab it there rather than making a second trip.', 5, 15),
                        ],
                    ],
                ],
            ],

            // ================= DAY 4 =================
            [
                'day_number' => 4,
                'date' => '2026-09-13',
                'title' => 'Kamakura, Enoshima & the flight out',
                'title_secondary' => '日曜日',
                'summary' => 'Kamakura\'s Great Buddha and hill temples, the anime-famous Enoden coast run, and Enoshima\'s shrine, tower and sea caves — a full day out that\'s still only an hour from Shinjuku, so a late start or a passing storm just trims the edges. Bags wait at the hotel.',
                'weather_tag' => 'outdoor',
                'forecast_date' => '2026-09-13',
                'temp_high' => 29,
                'temp_low' => 23,
                'weather_note' => 'Coast — a touch cooler with a sea breeze, showers pass quickly. The closest of the four trips and the easiest to shorten: you loop back through Shinjuku before the late-night run to Haneda for the 01:00 flight.',
                'outfit_chips' => ['breathable outfit', 'layer for the plane', 'easy shoes for temple steps', 'umbrella'],
                'outfit_photos' => [
                    ['url' => 'https://images.pexels.com/photos/9327156/pexels-photo-9327156.jpeg?auto=compress&cs=tinysrgb&w=600', 'label' => 'Idea 1', 'alt' => 'Flat loafers, crossbody bag'],
                    ['url' => 'https://images.pexels.com/photos/34976478/pexels-photo-34976478.jpeg?auto=compress&cs=tinysrgb&w=600', 'label' => 'Idea 2', 'alt' => 'Flats and crossbody bag'],
                    ['url' => 'https://images.pexels.com/photos/4271569/pexels-photo-4271569.jpeg?auto=compress&cs=tinysrgb&w=600', 'label' => 'Idea 3', 'alt' => 'Pleated skirt, sneakers'],
                ],
                'area_label' => 'Kamakura & Enoshima',
                'map_embed_url' => 'https://www.openstreetmap.org/export/embed.html?bbox=139.4700%2C35.2950%2C139.5700%2C35.3300&layer=mapnik&marker=35.3190%2C139.5470',
                'hiccups' => [
                    'Sunday makes Kamakura busy and the Enoden slow — start at Tsurugaoka Hachimangū by 09:00 and you\'ll be ahead of the worst of it.',
                    'The Enoden runs in rain but suspends in typhoon-strength wind; if the coast forecast is bad, swap this day and take one of the other trips on a calmer slot earlier in the week.',
                    'The last Airport Limousine buses from Shinjuku leave ~22:00–22:30 — if the day runs long, switch to the Shinagawa → Keikyū train, which runs later.',
                    'A 01:00 departure means Haneda\'s shops and some restaurants are shut — eat before you leave the city, or rely on Terminal 3\'s 24-hour options.',
                ],
                'stops' => [
                    [
                        'time' => '07:30', 'title' => 'Checkout — bags held at the hotel',
                        'description' => 'The flight is 01:00, so bags travel with you today. The front desk holds luggage until the evening — you\'ll pick it up before Haneda.',
                    ],
                    [
                        'time' => '08:00', 'title' => 'Train to Kamakura', 'cost_label' => '~$7 rail',
                        'description' => 'JR Shōnan–Shinjuku Line direct, ~1 hour. Buy the Enoden "Noriorikun" 1-day pass (¥800) at Kamakura Station for unlimited hops on the coastal line.',
                    ],
                    [
                        'time' => '09:00', 'title' => 'Tsurugaoka Hachimangū + Komachi-dōri', 'cost_label' => 'free',
                        'description' => 'Kamakura\'s central shrine at the head of a long approach avenue. Walk back down through Komachi-dōri, the covered snack-and-craft street — a coffee and a taiyaki. Do the outdoor temple time now, before the heat and the crowds.',
                    ],
                    [
                        'time' => '10:30', 'title' => 'Enoden two stops to Hase — Great Buddha + Hasedera', 'cost_label' => '~$5 admissions',
                        'description' => 'Kōtoku-in: the 11.4-metre bronze Great Buddha (1252), sitting in the open air since a tsunami took its hall in 1498 — step inside it for ¥50. Five minutes\' walk away, Hasedera has a hillside of Kannon statues and a sea view.',
                    ],
                    [
                        'time' => '12:30', 'title' => 'Lunch in Hase / Kamakura — shirasu bowl', 'cost_label' => '~$10–18 food',
                        'description' => 'The Shōnan-coast dish: raw or boiled baby sardines over rice. Most cafés along the Hase and Kamakura streets do a version.',
                    ],
                    [
                        'time' => '13:45', 'title' => 'Enoden along the coast to Enoshima', 'cost_label' => 'pass covers it',
                        'description' => 'The little green train runs right along the seawall — it passes Kamakurakōkō-mae, the level crossing with the ocean behind it that every anime opening seems to use. Get off at Enoshima and walk the bridge to the island.',
                    ],
                    [
                        'time' => '14:15', 'title' => 'On Enoshima — pick your depth', 'cost_label' => 'free–$19',
                        'option_label' => 'Pick one',
                        'options' => [
                            $this->opt('Shrine climb → Sea Candle → Iwaya Caves', 'mid', 'the full island traverse, ~2 hr · ¥360 caves · ¥500 tower · ¥360 escalator', 'Up past the Benzaiten shrine (paid outdoor escalators if you\'d rather not climb), out to the lighthouse-style Sea Candle tower, then down the far side to the wave-cut Iwaya sea caves — cool, lantern-lit.', 8, 12, true),
                            $this->opt('Shrine + Sea Candle only', 'budget', '~1 hr · ¥500 tower', 'Skip the long walk down to the caves and back — just the shrine and the view, then back over the bridge.', 4, 4),
                            $this->opt('Enoshima Aquarium (mainland side)', 'splurge', 'by the bridge, not on the island · ¥2,800', 'If it\'s genuinely pouring, skip the island and do the jellyfish hall and the Sagami Bay tank instead — fully indoors, right by the station.', 19, 19),
                        ],
                    ],
                    [
                        'time' => '16:30', 'title' => 'Back to Shinjuku', 'cost_label' => '~$8 rail',
                        'description' => 'Odakyū from Katase-Enoshima direct to Shinjuku, ~1h10 (or the "Enoshima" Romancecar, ~1h + ¥750). Arrive ~17:45–18:15.',
                    ],
                    [
                        'time' => '18:30', 'title' => 'Collect bags, farewell dinner near Shinjuku', 'cost_label' => '~$12–30 food',
                        'description' => 'Grab the luggage from the front desk, then a last proper meal close to the station — tonkatsu, a sushi counter, or a spread from the Odakyū / Isetan depachika.',
                    ],
                    [
                        'time' => '19:45', 'title' => 'Last shopping run — the take-home haul', 'cost_label' => '~$60–200',
                        'description' => 'The only window left, and the reason Day 1 was a scouting trip. The bags are already with you, so buy to fit what\'s left. Bring the passports — tax-free is per store, per day, ¥5,000 minimum.',
                        'option_label' => 'Where to buy',
                        'options' => [
                            $this->opt('Don Quijote Shinjuku East', 'budget', '~3-min walk — same block · open 24 hours', 'The bulk sweep, and the only thing still open once everything else has closed: regional KitKats, snacks, cosmetics, character goods, cheap duffels. Tax-free counter upstairs.', 40, 80, true),
                            $this->opt('Isetan depachika, Shinjuku', 'splurge', 'Shinjuku 3-chōme · closes 20:00', 'The best food hall in Tokyo — boxed sweets, tea, senbei, pickles, all gift-wrapped and built to travel. Go here first, at 19:45 sharp, because it\'s the one that closes.', 30, 90),
                            $this->opt('Yodobashi Camera, Shinjuku West', 'splurge', '~8-min walk · till 22:00', 'If Day 1 turned up something worth carrying home, this is when you actually buy it. Only worth the walk for a real purchase.', 50, 200),
                            $this->opt('Skip it — the bags are full', 'budget', 'an extra hour at Haneda instead', 'A legitimate choice by night four.', 0, 0),
                        ],
                    ],
                    [
                        'time' => '20:45', 'title' => 'Head to Haneda Airport', 'cost_label' => '~$5–13 transport',
                        'option_label' => 'How to get there',
                        'options' => [
                            $this->opt('Airport Limousine Bus from Busta Shinjuku', 'mid', '~45–60 min · luggage in the hold · ¥1,400', 'One seat straight to Terminal 3. Last departures ~22:00–22:30 — the reason dinner is early.', 9, 9, true),
                            $this->opt('JR to Shinagawa → Keikyū to Haneda', 'budget', '~45 min, one transfer · ¥660', 'Runs later than the buses (until ~23:30) — the fallback if the day overran.', 4, 5),
                            $this->opt('Taxi, direct', 'splurge', '~40–55 min at that hour · ¥8,000–10,000', 'Door to door if the day fell apart.', 55, 70),
                        ],
                    ],
                    [
                        'time' => '22:00', 'title' => 'Haneda Terminal 3 — check-in, security, immigration',
                        'description' => 'Check-in usually opens 3 hours before departure. Terminal 3 has 24-hour dining, a convenience store, and an airside observation deck if you\'re early.',
                    ],
                    [
                        'time' => '01:00', 'title' => 'Depart — PR 422 to Manila',
                        'description' => 'Lands 05:05. Trip complete.',
                    ],
                ],
            ],
        ];
    }

    private function budgetLines(): array
    {
        return [
            ['category' => 'Rail & entry', 'label' => 'Day 1 — Chōfu Keiō return + Jindaiji bus + gardens', 'amount' => 10],
            ['category' => 'Rail & entry', 'label' => 'Day 2 — Okutama Chūō/Ōme rail + Nippara caves + bus', 'amount' => 27],
            ['category' => 'Rail & entry', 'label' => 'Day 3 — Mito Ltd express both ways + Kairakuen + Seaside Park', 'amount' => 34],
            ['category' => 'Rail & entry', 'label' => 'Day 4 — Enoden pass + temples + Sea Candle + caves', 'amount' => 22],
            ['category' => 'Shopping', 'label' => 'Stationery', 'note' => 'Sekaido, Loft', 'amount' => 30],
            ['category' => 'Shopping', 'label' => 'Clothing', 'note' => 'Uniqlo / GU, or Beams Japan', 'amount' => 60],
            ['category' => 'Shopping', 'label' => 'Food to bring home', 'note' => 'Ameyoko, Isetan, Mito nattō', 'amount' => 50],
            ['category' => 'Shopping', 'label' => 'Don Quijote sweep', 'note' => 'KitKats, snacks, cosmetics', 'amount' => 40],
            ['category' => 'Shopping', 'label' => 'Luggage for the haul', 'note' => 'a cheap duffel or an extra-bag fee', 'amount' => 25],
            ['category' => 'Meals', 'label' => 'Per diem', 'note' => '$15 / meal × 3 × 4 days', 'amount' => 180, 'per_person' => true],
            ['category' => 'Accommodation', 'label' => 'Shinjuku hotel', 'note' => 'twin rate ÷ 2 × 3 nights', 'amount' => 240],
            ['category' => 'Insurance', 'label' => 'Travel insurance', 'note' => 'typhoon-season trip — get the delay cover', 'amount' => 35],
            ['category' => 'Transpo', 'label' => 'Round-trip airfare', 'note' => 'MNL ⇄ HND', 'amount' => 330],
            ['category' => 'Transpo', 'label' => 'PH travel tax + terminal fee', 'amount' => 35],
            ['category' => 'Transpo', 'label' => 'Suica — in-Tokyo hops', 'amount' => 30],
            ['category' => 'Transpo', 'label' => 'Airport ⇄ Shinjuku transfers', 'note' => 'bus × 2', 'amount' => 18],
            ['category' => 'Transpo', 'label' => 'Weather slack', 'note' => 'rebook if a trip is stormed out', 'amount' => 25],
            ['category' => 'Communication', 'label' => 'eSIM / pocket wifi', 'note' => '~6 days', 'amount' => 15],
        ];
    }
}
