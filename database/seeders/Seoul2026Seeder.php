<?php

namespace Database\Seeders;

use App\Models\BudgetLine;
use App\Models\Stop;
use App\Models\StopOption;
use App\Models\Trip;
use App\Models\TripDay;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * The hand-built Seoul 2026 itinerary (webbywife.github.io/sk2026), ported into
 * the reusable Travel2gether schema. This is the reference trip that proves the
 * pattern is data-driven rather than hand-coded per city.
 */
class Seoul2026Seeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            Trip::where('slug', 'seoul-2026')->delete();

            $trip = Trip::create([
                'slug' => 'seoul-2026',
                'title' => 'Seoul 2026',
                'tagline' => 'AI Summit Mission',
                'subhead' => 'Five days, one AI Summit, one campus visit, and a city that runs on neon and 1,000-year-old temples. Tap through each day below.',
                'destination' => 'Seoul, South Korea',
                'origin_label' => 'MNL -> ICN -> MNL',
                'start_date' => '2026-08-18',
                'end_date' => '2026-08-22',
                'party_size' => 2,
                'currency' => 'USD',
                'map_provider' => 'google',
                'lat' => 37.5115,   // Samseong-dong / COEX, where the hotel is
                'lon' => 127.0595,
                'forecast_note' => 'The numbers on each day are climate averages for late August in Seoul — hot, muggy, high UV, with roughly a 1-in-3 chance of a shower any given day. The live forecast fills in real numbers once the dates fall inside Open-Meteo\'s ~16-day window.',
                'segments' => [
                    [
                        'from' => 'MNL', 'to' => 'ICN', 'date' => 'TUE 18 AUG 2026',
                        'depart' => '00:55', 'arrive' => '06:00', 'terminal' => 'T1 -> T1',
                        'airline' => 'Philippine Airlines', 'flight_no' => 'PR400',
                    ],
                    [
                        'from' => 'ICN', 'to' => 'MNL', 'date' => 'SAT 22 AUG 2026',
                        'depart' => '20:30', 'arrive' => '23:40', 'terminal' => 'T1 -> T1',
                        'airline' => 'Philippine Airlines', 'flight_no' => 'PR403',
                    ],
                ],
                'stats' => [
                    ['value' => '5', 'label' => 'days in Seoul'],
                    ['value' => '3', 'label' => 'AI Summit days'],
                    ['value' => '1', 'label' => 'campus visit'],
                    ['value' => '18h', 'label' => 'total flight time'],
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

                    $record = $tripDay->stops()->create($stop + [
                        'sort' => $j,
                        'has_options' => count($options) > 0,
                    ]);

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

    private function opt(string $name, ?string $tier, ?string $meta, string $note, ?int $min, ?int $max, ?string $mapUrl = null, bool $pick = false): array
    {
        return [
            'name' => $name,
            'tier' => $tier,
            'note' => trim(($meta ? "$meta — " : '') . $note),
            'cost_min' => $min,
            'cost_max' => $max,
            'map_url' => $mapUrl,
            'is_default_pick' => $pick,
        ];
    }

    private function days(): array
    {
        return [
            // ================= DAY 1 =================
            [
                'day_number' => 1,
                'date' => '2026-08-18',
                'title' => 'Touchdown & Sejong University',
                'title_secondary' => '화요일',
                'summary' => 'The Sejong University visit is fixed at 10am, so today is built around it — a quick bag-drop and breakfast near the hotel, out to campus and back, then check-in and rest before the welcome dinner.',
                'weather_tag' => 'outdoor',
                'forecast_date' => '2026-08-18',
                'temp_high' => 28,
                'temp_low' => 22,
                'weather_note' => 'Hot, humid, ~35% rain chance. Gyeongbokgung is closed on Tuesdays anyway, so no loss — today stays close to the hotel while you shake off the flight.',
                'outfit_chips' => ['breathable tee', 'light layer for AC', 'walking shoes', 'compact umbrella'],
                'outfit_photos' => [
                    ['url' => 'https://images.pexels.com/photos/934063/pexels-photo-934063.jpeg?auto=compress&cs=tinysrgb&w=600', 'label' => 'Idea 1', 'alt' => 'Striped top, jeans, sunglasses'],
                    ['url' => 'https://images.pexels.com/photos/28645956/pexels-photo-28645956.jpeg?auto=compress&cs=tinysrgb&w=600', 'label' => 'Idea 2', 'alt' => 'Sneakers, sunglasses, tote bag'],
                    ['url' => 'https://images.pexels.com/photos/3944690/pexels-photo-3944690.jpeg?auto=compress&cs=tinysrgb&w=600', 'label' => 'Idea 3', 'alt' => 'Jeans, scarf, sneakers'],
                ],
                'area_label' => 'Sejong University',
                'map_embed_url' => 'https://www.openstreetmap.org/export/embed.html?bbox=127.03811%2C37.50406%2C127.08010%2C37.55527&layer=mapnik&marker=37.54927%2C127.07410',
                'hiccups' => [
                    'Immigration at Incheon can run 30–45+ min on a busy morning — if the 06:00 landing slips, the 09:30 taxi to Sejong University is the one fixed appointment today, so protect that time first and compress breakfast if needed.',
                    'Lunch has to happen around the Sejong University visit rather than at a set time — grab something near campus before 10am or right after 2pm.',
                    'Room check-in isn\'t guaranteed right at 15:00 if the previous guest\'s room isn\'t ready — the bell desk can hold your bags longer, no need to hover in the lobby.',
                ],
                'stops' => [
                    [
                        'time' => '06:00', 'title' => 'Land at Incheon T1', 'cost_label' => '~$9–15 transport',
                        'description' => 'Immigration + bags, then Airport Limousine Bus #6103 — non-stop to the COEX City Airport Terminal, 2 min from the hotel. ~62 km, ~65–90 min. Board at Level 1 Arrivals, Gate 6–7, Platform 7A.',
                        'map_url' => 'https://maps.google.com/?cid=10181407952118529748',
                    ],
                    [
                        'time' => '08:30', 'title' => 'Bag drop at Shilla Stay Samsung COEX',
                        'description' => 'Check-in isn\'t until 3pm — the bell desk will hold your luggage. This is your base all four nights.',
                        'map_url' => 'https://maps.google.com/?cid=1342291557989927897',
                    ],
                    [
                        'time' => '08:45', 'title' => 'Breakfast near the hotel', 'cost_label' => '~$3–10 food',
                        'option_label' => 'Breakfast options',
                        'options' => [
                            $this->opt('Four Stones Coffee Roasters', 'mid', '~0.3 km · 4-min walk from hotel', 'Quiet third-wave roastery, opens 7:30am. Good pour-over and a small pastry case — least crowded of the three.', 8, 10, 'https://maps.google.com/?cid=8842083408563454710', true),
                            $this->opt('Isaac Toast, Samseong', 'budget', '~0.4 km · 5-min walk from hotel', 'Korean-style toasted egg sandwich chain — the cheapest, fastest option if you just want to eat and get moving.', 3, 5, 'https://www.google.com/maps/search/?api=1&query=Isaac+Toast+Samseong+Seoul'),
                            $this->opt('Paris Baguette, COEX', 'indoor-ac', '~0.2 km · inside COEX Mall', 'Reliable Korean bakery chain with AC seating — useful if it\'s already muggy out and you\'d rather sit indoors.', 6, 9, 'https://www.google.com/maps/search/?api=1&query=Paris+Baguette+COEX+Seoul'),
                        ],
                    ],
                    [
                        'time' => '09:30', 'title' => 'Taxi to Sejong University', 'cost_label' => '~$9–11 transport',
                        'description' => '~4.5 km, ~15–20 min by taxi from COEX — cutting it close after a red-eye, but keeps the 10am start on time.',
                        'map_url' => 'https://maps.google.com/?cid=1908076492013036779',
                    ],
                    [
                        'time' => '10:00–14:00', 'title' => 'Sejong University visit',
                        'description' => 'Meeting/visit on campus — lunch works best grabbed near campus before or after, since this runs straight through midday.',
                    ],
                    [
                        'time' => '14:15', 'title' => 'Taxi back to the hotel',
                        'description' => '~15–20 min back to Shilla Stay Samsung COEX.',
                    ],
                    [
                        'time' => '15:00', 'title' => 'Official check-in, rest / shower off the flight',
                        'description' => '45–60 min is plenty if you slept a little on the plane.',
                    ],
                    [
                        'time' => '16:00', 'title' => 'Shopping nearby', 'cost_label' => 'free browsing–$40',
                        'option_label' => 'Where to shop',
                        'options' => [
                            $this->opt('COEX Mall / Starfield', 'indoor', 'same building as the hotel', 'Jet-lagged first day pick — fully indoor, air-conditioned, literally downstairs. Kakao Friends flagship, Olive Young, Artbox stationery, and Innisfree on the same floor.', 0, 40, 'https://www.google.com/maps/search/?api=1&query=Kakao+Friends+COEX+Seoul', true),
                        ],
                    ],
                    [
                        'time' => '19:00', 'title' => 'Welcome dinner', 'cost_label' => '~$5–35 food',
                        'option_label' => 'Dinner options',
                        'options' => [
                            $this->opt('Sam\'s Korean BBQ', 'mid', '~0.4 km · 5-min walk from hotel', 'Easy, reliable first-night BBQ — tabletop grilling, generous banchan spread, no reservation drama.', 25, 35, 'https://maps.google.com/?cid=8925633591907172216', true),
                            $this->opt('Yoogane Dakgalbi, Gangnam', 'budget', '~0.6 km · 8-min walk', 'Spicy stir-fried chicken and rice cakes, casual and filling. Good if you\'re saving the splurge dinner for later in the trip.', 12, 18, 'https://www.google.com/maps/search/?api=1&query=Yoogane+Dakgalbi+Gangnam+Seoul'),
                            $this->opt('Maple Tree House, Gangnam', 'splurge', '~1 km · 10-min taxi', 'A well-loved higher-end BBQ institution — better cuts, better banchan, worth it to start the trip with a splurge instead of ending on one.', 35, 45, 'https://www.google.com/maps/search/?api=1&query=Maple+Tree+House+Gangnam+Seoul'),
                        ],
                    ],
                ],
            ],

            // ================= DAY 2 =================
            [
                'day_number' => 2,
                'date' => '2026-08-19',
                'title' => 'Summit day one',
                'title_secondary' => '수요일',
                'summary' => 'Registration lines on the first morning of a multi-day summit are often the worst of the whole event — the 08:45 walk-over beats arriving right at 09:00.',
                'weather_tag' => 'covered',
                'forecast_date' => '2026-08-19',
                'temp_high' => 28,
                'temp_low' => 22,
                'weather_note' => 'Warm outside, cold inside. Conference halls run heavily air-conditioned — the walk to COEX will feel muggy, the ballroom will feel like a fridge.',
                'outfit_chips' => ['smart-casual top', 'light cardigan / blazer', 'comfortable trousers'],
                'outfit_photos' => [
                    ['url' => 'https://images.pexels.com/photos/934070/pexels-photo-934070.jpeg?auto=compress&cs=tinysrgb&w=600', 'label' => 'Idea 1', 'alt' => 'White sherpa cardigan, jeans, glasses'],
                    ['url' => 'https://images.pexels.com/photos/15667095/pexels-photo-15667095.jpeg?auto=compress&cs=tinysrgb&w=600', 'label' => 'Idea 2', 'alt' => 'Cream knit cardigan, jeans'],
                    ['url' => 'https://images.pexels.com/photos/8318230/pexels-photo-8318230.jpeg?auto=compress&cs=tinysrgb&w=600', 'label' => 'Idea 3', 'alt' => 'Comfortable trousers, floral flats'],
                ],
                'area_label' => 'COEX Grand Ballroom',
                'map_embed_url' => 'https://www.openstreetmap.org/export/embed.html?bbox=127.03508%2C37.50390%2C127.06452%2C37.53167&layer=mapnik&marker=37.51307%2C127.05852',
                'hiccups' => [
                    'Registration lines on the first morning of a multi-day summit are often the worst of the whole event — sticking to the 08:45 walk-over rather than arriving right at 09:00 avoids the worst of it.',
                    'Ballrooms run cold even in summer — if you forgot a light layer, COEX Mall (one floor down) has plenty of clothing shops.',
                    'BBQ spots near office districts can have a short wait after 19:00 on weekdays — Kimbap Cheonguk is the fast, no-wait backup if you\'re too hungry to queue.',
                ],
                'stops' => [
                    [
                        'time' => '08:00', 'title' => 'Breakfast near the hotel', 'cost_label' => '~$5–12 food',
                        'option_label' => 'Breakfast options',
                        'options' => [
                            $this->opt('Jeonu Sogogi Haejangguk', 'mid', '~0.4 km · 5-min walk from hotel', 'Hearty beef soup, opens 8am — sets you up for a long conference day.', 10, 12, 'https://maps.google.com/?cid=13408439636498587448', true),
                            $this->opt('Egg Drop, Samseong', 'budget', '~0.3 km · 4-min walk', 'Fast breakfast sandwich chain — best if you\'re running behind and want to eat on the walk over.', 5, 7, 'https://www.google.com/maps/search/?api=1&query=Egg+Drop+Samseong+Seoul'),
                            $this->opt('Twosome Place, Samseong', 'indoor-ac', '~0.3 km · 4-min walk', 'Popular Korean cafe chain — coffee plus a light pastry, comfortable AC seating for a few quiet minutes before the ballroom.', 6, 9, 'https://www.google.com/maps/search/?api=1&query=Twosome+Place+Samseong+Seoul'),
                        ],
                    ],
                    [
                        'time' => '08:45', 'title' => 'Walk to COEX Grand Ballroom',
                        'description' => '10 minutes on foot — no transport needed.',
                        'map_url' => 'https://maps.google.com/?cid=8637059584940914539',
                    ],
                    ['time' => '09:00–18:00', 'title' => 'AI Summit Seoul & Expo — sessions'],
                    [
                        'time' => '19:00', 'title' => 'Dinner near Gangnam', 'cost_label' => '~$5–30 food',
                        'option_label' => 'Dinner options',
                        'options' => [
                            $this->opt('Hyeongje Teuksubuwi Gangnam', 'mid', '~2.5 km · 10-min taxi from COEX', 'High-energy Korean BBQ — the chef grills tableside and walks you through every cut. Perfect 5.0 rating, always lively.', 25, 30, 'https://maps.google.com/?cid=5013849398014679674', true),
                            $this->opt('Kimbap Cheonguk, Gangnam', 'budget', '~0.5 km · 6-min walk from COEX', 'Ultra-budget Korean diner chain — kimbap, ramyeon, dumplings. Good if you\'re tired after a full day of sessions and want something fast, no taxi needed.', 5, 8, 'https://www.google.com/maps/search/?api=1&query=Kimbap+Cheonguk+Gangnam+Seoul'),
                            $this->opt('Palsaik Samgyeopsal, Gangnam', 'mid', '~1.8 km · 8-min taxi from COEX', 'Colorful marinated pork belly BBQ chain — a different flavor angle from a standard galbi dinner, still tabletop grilled.', 20, 28, 'https://www.google.com/maps/search/?api=1&query=Palsaik+Samgyeopsal+Gangnam+Seoul'),
                        ],
                    ],
                    [
                        'time' => '20:45', 'title' => 'Shopping nearby', 'cost_label' => 'free browsing–$40',
                        'option_label' => 'Where to shop',
                        'options' => [
                            $this->opt('Sinsa / Garosu-gil', 'mid', '~2 km · 10-min taxi from dinner', 'Fashion boutiques, lifestyle stores, beauty, cafés — a tree-lined street with an Olive Young branch, Zara, and dozens of small curated Korean shops. More relaxed and photogenic than a mall.', 0, null, 'https://www.google.com/maps/search/?api=1&query=Garosu-gil+Seoul', true),
                            $this->opt('Gangnam Station Underground Arcade', 'budget', '~10-min subway from Samseong', 'Inexpensive Korean clothes, accessories, shoes, and small finds — much more budget-friendly than the boutique side of Gangnam, plus street-food stalls along the way.', 5, 8, 'https://www.google.com/maps/search/?api=1&query=Gangnam+Station+Underground+Shopping+Arcade+Seoul'),
                        ],
                    ],
                    [
                        'time' => '21:30', 'title' => 'Evening, low-key', 'cost_label' => 'free–$10',
                        'option_label' => 'Evening options',
                        'options' => [
                            $this->opt('SMTOWN COEX Artium', 'mid', '~0.5 km · 6-min walk from dinner', 'K-pop themed exhibits, shop, and cafe inside COEX — an easy, low-effort way to close the night without leaving the neighborhood.', 5, 10, 'https://www.google.com/maps/search/?api=1&query=SMTOWN+COEX+Artium+Seoul', true),
                            $this->opt('Bongeunsa Temple, evening', 'budget', '~1 km · 12-min walk from dinner', 'Quiet, lantern-lit grounds at night — calming after a packed conference day, and you already know the layout from Day 1.', 0, 0, 'https://maps.google.com/?cid=5001419286940765545'),
                        ],
                    ],
                ],
            ],

            // ================= DAY 3 =================
            [
                'day_number' => 3,
                'date' => '2026-08-20',
                'title' => 'Summit + Namdaemun & Namsan',
                'title_secondary' => '목요일',
                'summary' => 'A national treasure, Korea\'s oldest market, and the city\'s best skyline view all sit inside one 15-minute walking loop — one subway ride from the summit venue.',
                'weather_tag' => 'outdoor',
                'forecast_date' => '2026-08-20',
                'temp_high' => 28,
                'temp_low' => 22,
                'weather_note' => 'Same pattern, evening loop adds sweat. Namdaemun and Namsan stay warm and crowded at night — breathable fabric matters more than style here.',
                'outfit_chips' => ['conference layers', 'change into casual tee for evening', 'crossbody bag over backpack'],
                'outfit_photos' => [
                    ['url' => 'https://images.pexels.com/photos/9327156/pexels-photo-9327156.jpeg?auto=compress&cs=tinysrgb&w=600', 'label' => 'Idea 1', 'alt' => 'Flat loafers, structured crossbody bag'],
                    ['url' => 'https://images.pexels.com/photos/34976478/pexels-photo-34976478.jpeg?auto=compress&cs=tinysrgb&w=600', 'label' => 'Idea 2', 'alt' => 'Flats and crossbody bag'],
                    ['url' => 'https://images.pexels.com/photos/4271569/pexels-photo-4271569.jpeg?auto=compress&cs=tinysrgb&w=600', 'label' => 'Idea 3', 'alt' => 'Pleated skirt, sneakers, casual evening look'],
                ],
                'area_label' => 'Namsan / N Seoul Tower',
                'map_embed_url' => 'https://www.openstreetmap.org/export/embed.html?bbox=126.96931%2C37.50707%2C127.06452%2C37.57532&layer=mapnik&marker=37.55659%2C126.98397',
                'hiccups' => [
                    'Namsan Cable Car queues can run 30–45 min at peak evening hours in summer — the free hiking trail option skips the line entirely if you\'re short on time.',
                    'Summer haze can dull the view from N Seoul Tower\'s observatory on humid nights — the city lights are still worth it even without a crystal-clear skyline.',
                    'Some Namdaemun Market stalls close by 18:00–19:00 depending on the vendor — if you arrive later than planned, Myeongdong Street Food Alley stays open later.',
                ],
                'stops' => [
                    [
                        'time' => '08:00', 'title' => 'Breakfast near the hotel', 'cost_label' => '~$4–10 food',
                        'option_label' => 'Breakfast options',
                        'options' => [
                            $this->opt('Le Four Doré', 'mid', '~0.5 km · 6-min walk from hotel', 'French-style bakery, opens 7:30am — a change of pace from Korean breakfast soup.', 8, 10, 'https://maps.google.com/?cid=3505816711078296040', true),
                            $this->opt('Mom\'s Touch, Samseong', 'budget', '~0.3 km · 4-min walk', 'Korean fast-food chain breakfast set — cheapest option, good if the last two days\' breakfasts ran long.', 4, 6, 'https://www.google.com/maps/search/?api=1&query=Mom%27s+Touch+Samseong+Seoul'),
                            $this->opt('Twosome Place, Samseong', 'indoor-ac', '~0.3 km · 4-min walk', 'Reliable Korean cafe chain — coffee and a light pastry if you want a quieter sit-down before another long conference day.', 6, 9, 'https://www.google.com/maps/search/?api=1&query=Twosome+Place+Samseong+Seoul'),
                        ],
                    ],
                    ['time' => '08:45', 'title' => 'Walk to COEX Grand Ballroom', 'description' => '10 minutes on foot.'],
                    ['time' => '09:00–18:00', 'title' => 'AI Summit Seoul & Expo — sessions'],
                    [
                        'time' => '18:15', 'title' => 'Subway Line 2 to Euljiro 1(il)-ga', 'cost_label' => '~$1.50 transport',
                        'description' => 'Direct from Samseong, no transfer, ~9.5 km, ~20 min.',
                    ],
                    [
                        'time' => '18:45', 'title' => 'Evening loop, stop one', 'cost_label' => 'free–$1',
                        'option_label' => 'Evening loop options',
                        'options' => [
                            $this->opt('Sungnyemun Gate', 'budget', '~0.3 km · 5-min walk from station', 'National Treasure No. 1 — Korea\'s old south gate, lit up beautifully at night.', 0, 0, 'https://maps.google.com/?cid=13660656182679921379', true),
                            $this->opt('Deoksugung Palace & Stone Wall Walkway', 'budget', '~0.7 km · 9-min walk from station', 'A quieter Joseon palace than Gyeongbokgung, with a famous tree-lined stone wall path along the outside — lovely at dusk.', 1, 1, 'https://www.google.com/maps/search/?api=1&query=Deoksugung+Palace+Stone+Wall+Walkway+Seoul'),
                            $this->opt('Cheonggyecheon Stream', 'budget', '~0.6 km · 8-min walk from station', 'Illuminated urban stream walk running through downtown — cooling, scenic, and a favorite evening spot for locals.', 0, 0, 'https://www.google.com/maps/search/?api=1&query=Cheonggyecheon+Stream+Seoul'),
                        ],
                    ],
                    [
                        'time' => '19:15', 'title' => 'Evening loop, snack stop', 'cost_label' => '~$8–15 food',
                        'option_label' => 'Snack options',
                        'options' => [
                            $this->opt('Namdaemun Market — hotteok & kalguksu', 'budget', '~0.2 km from the gate', 'Korea\'s oldest traditional market — sweet pancakes and knife-cut noodle soup, quick and cheap.', 8, 12, 'https://maps.google.com/?cid=14787317685547704417', true),
                            $this->opt('Myeongdong Street Food Alley', 'budget', '~1 km · 12-min walk from the gate', 'Tteokbokki, gyeran-bbang, cheese hotdogs — grazing food, and it\'s already on the way toward Namsan.', 8, 12, 'https://www.google.com/maps/search/?api=1&query=Myeongdong+Street+Food+Seoul'),
                            $this->opt('Bukchang-dong Grilled Fish Alley', 'mid', '~0.8 km · 10-min walk from the gate', 'Famous grilled-mackerel alley near City Hall — more of a sit-down meal than a snack, good if you skip a full dinner tonight.', 10, 15, 'https://www.google.com/maps/search/?api=1&query=Bukchang-dong+Grilled+Fish+Alley+Seoul'),
                        ],
                    ],
                    [
                        'time' => '20:15', 'title' => 'Evening loop, Namsan', 'cost_label' => 'free–$20',
                        'option_label' => 'Namsan options',
                        'options' => [
                            $this->opt('N Seoul Tower via Namsan Cable Car', 'mid', '~1.3 km · 10-min walk to base station', 'Night views over the whole city — cable car runs until 11pm.', 11, 11, 'https://maps.google.com/?cid=555211272804245478', true),
                            $this->opt('Namsan Park free trail + Locks of Love', 'budget', 'same starting point', 'Walk or escalator up instead of the cable car, skip the paid observation deck — same park, same locks-of-love terrace, no ticket needed.', 0, 0, 'https://www.google.com/maps/search/?api=1&query=Namsan+Park+Locks+of+Love+Seoul'),
                            $this->opt('N Seoul Tower Observatory + terrace drink', 'splurge', 'same base station', 'Add the indoor observatory deck and a drink at the terrace cafe on top of the cable car ticket — the fuller version of tonight\'s view.', 15, 20, 'https://www.google.com/maps/search/?api=1&query=N+Seoul+Tower+Observatory+Seoul'),
                        ],
                    ],
                    [
                        'time' => '21:15', 'title' => 'Shopping nearby: Myeongdong', 'cost_label' => 'free–$12',
                        'option_label' => 'Where to shop',
                        'options' => [
                            $this->opt('Myeongdong Shopping Street + Olive Young', 'mid', 'short walk down from Namsan', 'Beauty and mid-range fashion in one strip — cosmetics shops open until 22:30, with the Olive Young flagship plus Nature Republic, Innisfree, Daiso, and a Line Friends store within a couple of blocks.', 0, null, 'https://maps.google.com/?cid=4270375483740491276', true),
                            $this->opt('Myeongdong Cathedral', 'budget', '~0.3 km from the shopping street', 'A quiet, historic Gothic cathedral just off the main strip — a nice contrast to the shopping crowds, exterior view at night.', 0, 0, 'https://www.google.com/maps/search/?api=1&query=Myeongdong+Cathedral+Seoul'),
                            $this->opt('Sulbing dessert cafe, Myeongdong', 'mid', '~0.2 km from the shopping street', 'Popular Korean shaved-ice (bingsu) chain — a good way to cool down after a full day of walking in the heat.', 8, 12, 'https://www.google.com/maps/search/?api=1&query=Sulbing+Myeongdong+Seoul'),
                        ],
                    ],
                    [
                        'time' => '22:30', 'title' => 'Back to hotel', 'cost_label' => '~$1.50 transport',
                        'description' => 'Line 4 to City Hall, transfer Line 2 to Samseong, ~25 min. Or taxi, ~20 min.',
                    ],
                ],
            ],

            // ================= DAY 4 =================
            [
                'day_number' => 4,
                'date' => '2026-08-21',
                'title' => 'Summit finale + free afternoon + Banpo',
                'title_secondary' => '금요일',
                'summary' => 'Dinner and the fountain show happen at the same spot, so there\'s no second trip — you eat, the sun goes down, and the show starts right where you\'re sitting. A memorable close to the summit for the price of a casual dinner.',
                'weather_tag' => 'outdoor',
                'forecast_date' => '2026-08-21',
                'temp_high' => 27,
                'temp_low' => 21,
                'weather_note' => 'Slightly milder, still humid. An open afternoon by day, riverside dinner and a fountain show by night — casual, comfortable clothes work for both.',
                'outfit_chips' => ['smart-casual by day', 'casual outfit for the riverside', 'light layer for the evening breeze'],
                'outfit_photos' => [
                    ['url' => 'https://images.pexels.com/photos/3214308/pexels-photo-3214308.jpeg?auto=compress&cs=tinysrgb&w=600', 'label' => 'Idea 1', 'alt' => 'Floral midi dress, sneakers'],
                    ['url' => 'https://images.pexels.com/photos/38627814/pexels-photo-38627814.jpeg?auto=compress&cs=tinysrgb&w=600', 'label' => 'Idea 2', 'alt' => 'Printed dress, white sneakers'],
                    ['url' => 'https://images.pexels.com/photos/38273441/pexels-photo-38273441.jpeg?auto=compress&cs=tinysrgb&w=600', 'label' => 'Idea 3', 'alt' => 'Floral dress, high-top sneakers'],
                ],
                'area_label' => 'Banpo Hangang Park',
                'map_embed_url' => 'https://www.openstreetmap.org/export/embed.html?bbox=126.98500%2C37.49800%2C127.07500%2C37.52500&layer=mapnik&marker=37.50930%2C126.99700',
                'hiccups' => [
                    'Closing sessions can run over — the free afternoon has nothing fixed, so it easily absorbs a late finish. Only the Banpo dinner and fountain show are time-sensitive.',
                    'Taxis get scarce right as the summit lets out, with many attendees requesting rides at once — have a ride-hailing app (Kakao T) ready as backup, or expect a longer wait.',
                    'The Banpo Bridge Rainbow Fountain doesn\'t run in heavy rain, high wind, or during dry-season water restrictions — check the schedule that afternoon before building the evening around it.',
                ],
                'stops' => [
                    [
                        'time' => '08:00', 'title' => 'Breakfast near the hotel', 'cost_label' => '~$3–12 food',
                        'option_label' => 'Breakfast options',
                        'options' => [
                            $this->opt('Cheongwaok Hakdong', 'mid', '~0.5 km · 7-min walk from hotel', 'Sundae gukbap, opens 8am — a heartier send-off before the closing sessions.', 10, 12, 'https://maps.google.com/?cid=7648116296943905782', true),
                            $this->opt('Isaac Toast, Samseong', 'budget', '~0.4 km · 5-min walk', 'Grab-and-go toasted sandwiches — quickest option if you want to get to the closing sessions early.', 3, 5, 'https://www.google.com/maps/search/?api=1&query=Isaac+Toast+Samseong+Seoul'),
                            $this->opt('Angel-in-us Coffee, Samseong', 'indoor-ac', '~0.3 km · 4-min walk', 'Korean cafe chain — coffee and a pastry, comfortable seating for a slower final-day-of-summit morning.', 6, 8, 'https://www.google.com/maps/search/?api=1&query=Angel-in-us+Coffee+Samseong+Seoul'),
                        ],
                    ],
                    ['time' => '08:45', 'title' => 'Walk to COEX Grand Ballroom', 'description' => '10 minutes on foot.'],
                    ['time' => '09:00–12:30', 'title' => 'Closing sessions at COEX'],
                    [
                        'time' => '13:00', 'title' => 'Lunch near COEX', 'cost_label' => '~$10–20 food',
                        'option_label' => 'Lunch options',
                        'options' => [
                            $this->opt('Banchan lunch, COEX Mall', 'mid', 'same building as the hotel', 'Easy sit-down Korean lunch right after the closing sessions let out — no need to go far.', 12, 18, null, true),
                            $this->opt('Gogung Bibimbap, COEX', 'budget', 'same building · COEX Mall', 'Famous Jeonju-style bibimbap chain — casual mall dining, faster turnaround than a sit-down restaurant.', 10, 14, 'https://www.google.com/maps/search/?api=1&query=Gogung+Bibimbap+COEX+Seoul'),
                        ],
                    ],
                    [
                        'time' => '14:00', 'title' => 'Free afternoon', 'cost_label' => 'free',
                        'option_label' => 'Free afternoon options',
                        'options' => [
                            $this->opt('Rest at the hotel', 'budget', 'right at the hotel', 'A genuinely open block after three straight summit days — nothing else is planned until the evening.', 0, 0, null, true),
                            $this->opt('Bongeunsa Temple, afternoon', 'budget', '~0.7 km · 8-min walk from COEX', 'If Day 1\'s morning visit got skipped for the Sejong University trip, this is the easiest make-up slot.', 0, 0, 'https://maps.google.com/?cid=5001419286940765545'),
                        ],
                    ],
                    [
                        'time' => '14:30', 'title' => 'Shopping nearby', 'cost_label' => 'free browsing–$100+',
                        'option_label' => 'Where to shop',
                        'options' => [
                            $this->opt('Express Bus Terminal Underground (Goto Mall)', 'budget', '~2.5 km · 10-min taxi from COEX', 'Inexpensive Korean clothes, accessories, shoes, and small finds — 620 shops along an 880m underground corridor, basic tops from ~$7. Much more budget-friendly than the luxury side of Gangnam.', 5, 20, 'https://www.google.com/maps/search/?api=1&query=Goto+Mall+Express+Bus+Terminal+Seoul', true),
                            $this->opt('COEX Mall / Starfield', 'indoor', 'same building as the hotel', 'The no-travel option — Kakao Friends flagship, Olive Young, Artbox, and Innisfree, all fully indoor and air-conditioned.', 0, 40, 'https://www.google.com/maps/search/?api=1&query=Kakao+Friends+COEX+Seoul'),
                            $this->opt('Sinsa / Garosu-gil', 'mid', '~3.5 km · 15-min taxi from COEX', 'Trendy + cafés — fashion boutiques, lifestyle stores, beauty, and more curated Korean brands than a straight budget mall.', 0, null, 'https://www.google.com/maps/search/?api=1&query=Garosu-gil+Seoul'),
                            $this->opt('Apgujeong / Dosan', 'splurge', '~4 km · 15-min taxi from COEX', 'Korean designer brands, beauty flagships, and Instagram-worthy stores — Dosan Park is the anchor for this stretch.', null, null, 'https://www.google.com/maps/search/?api=1&query=Dosan+Park+Apgujeong+Seoul'),
                            $this->opt('Insadong-gil', 'mid', '~8 km · 25–30 min taxi from COEX', 'Traditional Korean finds instead of fashion — hanji paper goods, pottery, tea, and folk crafts along a 700m car-free street. Worth the taxi for a souvenir run since Day 5\'s morning is now Seongsu.', 5, 60, 'https://www.google.com/maps/search/?api=1&query=Insadong+Seoul'),
                        ],
                    ],
                    [
                        'time' => '18:00', 'title' => 'Getting to Banpo Hangang Park', 'cost_label' => '~$1.50–13 transport',
                        'option_label' => 'How to get there',
                        'options' => [
                            $this->opt('Taxi, direct', 'mid', '~9.5 km from hotel', 'Fastest, easiest option — door to door in one ride.', 10, 13, null, true),
                            $this->opt('Subway + short walk', 'budget', 'Line 2 to Express Bus Terminal, transfer', 'About 35–40 minutes total including the walk into the park, air-conditioned the whole way.', 1, 2),
                            $this->opt('Ttareungyi city bike, partway', 'budget', 'bike dock near hotel', 'Cheapest and most scenic if the evening isn\'t too hot — dock near the park entrance and walk the last stretch.', 1, 1),
                        ],
                    ],
                    [
                        'time' => '18:30', 'title' => 'Dinner at Banpo', 'cost_label' => '~$5–25 food & drinks',
                        'option_label' => 'Dinner options',
                        'options' => [
                            $this->opt('Beer Garden, riverside', 'mid', 'right inside the park', 'The best seats for what\'s coming — order early to grab an outdoor spot before the show starts filling up.', 20, 25, 'https://maps.google.com/?cid=5651481207738318243', true),
                            $this->opt('Convenience-store ramen picnic', 'budget', 'CU/GS25 inside the park', 'The classic budget Han River picnic — instant ramen from the in-park convenience store cookers, mat on the lawn. A genuinely iconic cheap Seoul experience.', 5, 8),
                            $this->opt('Han River food-truck row', 'mid', 'along the riverside path', 'Rotating food trucks with casual fusion bites — a middle ground between the beer garden and a bare picnic.', 8, 15),
                        ],
                    ],
                    [
                        'time' => '19:30', 'title' => 'Banpo Bridge Rainbow Fountain show — best viewing spots', 'cost_label' => 'free',
                        'option_label' => 'Where to watch',
                        'options' => [
                            $this->opt('Riverside deck by the Beer Garden', 'budget', 'no need to move', 'You\'re already at the best straight-on view. Guinness World Record for longest bridge fountain, shows run every 30 min after dark.', 0, 0, 'https://maps.google.com/?cid=7196672692089808831', true),
                            $this->opt('Sevit Floating Island viewpoint', 'budget', '~1 km · 12-min walk east', 'A different angle on the fountain plus the illuminated floating island architecture itself — usually less crowded.', 0, 0, 'https://www.google.com/maps/search/?api=1&query=Sevit+Floating+Island+Seoul'),
                            $this->opt('Banpo Hangang Park lawn', 'budget', 'set back from the bridge', 'Farther from the action but far more relaxed — bring a mat if the deck areas look packed.', 0, 0),
                        ],
                    ],
                    [
                        'time' => '21:00', 'title' => 'Back to hotel',
                        'description' => '~20-min taxi from Banpo to Shilla Stay COEX.',
                    ],
                ],
            ],

            // ================= DAY 5 =================
            [
                'day_number' => 5,
                'date' => '2026-08-22',
                'title' => 'Jongno friends + Seongsu',
                'title_secondary' => '토요일',
                'summary' => 'Leaving your bags at the Jongno hotel means no backtrack to Shilla Stay — Seongsu covers perfume, makeup, clothes, and bags in one compact cluster of flagships, then a relaxed lunch send-off with friends before swinging back for luggage and heading to the airport.',
                'weather_tag' => 'outdoor',
                'forecast_date' => '2026-08-22',
                'temp_high' => 27,
                'temp_low' => 20,
                'weather_note' => 'Shopping morning, hanok afternoon. Highest rain chance of the trip late in August, so keep the umbrella handy for the Ikseon-dong stroll.',
                'outfit_chips' => ['sun hat / cap', 'sunscreen', 'breathable outfit for walking', 'umbrella in daypack'],
                'outfit_photos' => [
                    ['url' => 'https://images.pexels.com/photos/17900105/pexels-photo-17900105.jpeg?auto=compress&cs=tinysrgb&w=600', 'label' => 'Idea 1', 'alt' => 'Sun hat and sunscreen'],
                    ['url' => 'https://images.pexels.com/photos/7836547/pexels-photo-7836547.jpeg?auto=compress&cs=tinysrgb&w=600', 'label' => 'Idea 2', 'alt' => 'Sun hat, sunglasses, light sweater'],
                    ['url' => 'https://images.pexels.com/photos/3030690/pexels-photo-3030690.jpeg?auto=compress&cs=tinysrgb&w=600', 'label' => 'Idea 3', 'alt' => 'Sun hat, sunglasses, comfortable shoes'],
                ],
                'area_label' => 'Seongsu-dong',
                'map_embed_url' => 'https://www.openstreetmap.org/export/embed.html?bbox=126.98700%2C37.53900%2C127.06000%2C37.58200&layer=mapnik&marker=37.54450%2C127.05650',
                'hiccups' => [
                    'Seongsu is a real trip out from Jongno (~20–25 min each way) — the 09:15/12:00 taxi legs are the only fixed-ish points this morning, so leave extra buffer if traffic is heavy.',
                    'MUSINSA Megastore Seongsu opened in 2026 and draws weekend crowds — go early in the window (right after Tamburins/Olive Young) if you want it less packed.',
                    'Ikseon-dong\'s popular hanok cafes (익선취향 included) can have a wait at peak lunch hours — arrive right at 12:30, or fall back to Gwangjang Market if the line looks long.',
                    'Saturday evening traffic can slow the airport transfer from Jongno — confirm the limousine bus stop and schedule that morning (a different pickup point from the COEX bus used on arrival), and build in extra buffer after lunch.',
                ],
                'stops' => [
                    [
                        'time' => '07:30', 'title' => 'Early checkout — luggage in hand',
                        'description' => 'Flight isn\'t until 20:30, but bags travel with you today — you\'ll drop them off at the Jongno hotel next, not at Shilla Stay.',
                    ],
                    [
                        'time' => '08:00', 'title' => 'Taxi to Hotel the Designers Jongno to meet friends', 'cost_label' => '~$15–18 transport',
                        'description' => '~9.3 km, ~25–30 min. Leave your luggage at the front desk here for the day — no need to swing back to Shilla Stay before the airport.',
                        'map_url' => 'https://maps.google.com/?cid=1368431404401387531',
                    ],
                    [
                        'time' => '08:30', 'title' => 'Breakfast / coffee with friends', 'cost_label' => '~$5–18 food',
                        'option_label' => 'Breakfast options',
                        'options' => [
                            $this->opt('Cafes around Hotel the Designers Jongno', 'budget', 'right around the hotel', 'Plenty of casual coffee options nearby — easiest if the group just wants to catch up first before heading out to Seongsu.', 5, 8, null, true),
                            $this->opt('Tosokchon Samgyetang', 'mid', '~1 km · 12-min walk', 'Iconic ginseng chicken soup institution — a hearty, memorable brunch if the group wants a real sit-down meal instead of coffee.', 15, 18, 'https://www.google.com/maps/search/?api=1&query=Tosokchon+Samgyetang+Seoul'),
                            $this->opt('Cafe Onion, Anguk', 'mid', '~1 km · 12-min walk', 'Trendy hanok-building cafe, famous for its pastries — a good photogenic stop before the shopping morning.', 6, 10, 'https://www.google.com/maps/search/?api=1&query=Cafe+Onion+Anguk+Seoul'),
                        ],
                    ],
                    [
                        'time' => '09:15', 'title' => 'Taxi to Seongsu-dong', 'cost_label' => '~$10–13 transport',
                        'description' => '~7 km, ~20–25 min by taxi from Jongno. Subway also works — Jongno 3-ga to Seongsu on Line 2/3 with a transfer, ~30 min.',
                        'map_url' => 'https://www.google.com/maps/search/?api=1&query=Seongsu-dong+Seoul',
                    ],
                    [
                        'time' => '09:45', 'title' => 'Shopping nearby: Seongsu', 'cost_label' => 'free browsing–$100+',
                        'option_label' => 'Where to shop',
                        'options' => [
                            $this->opt('Tamburins Seongsu Flagship', 'splurge', 'near Seongsu Station Exit 4', 'For perfume — an iconic concrete-architecture flagship, the store people fly to Seoul to photograph. Niche fragrances, hand creams, and lip tints.', 20, 80, 'https://www.google.com/maps/search/?api=1&query=Tamburins+Seongsu+Seoul', true),
                            $this->opt('Olive Young N Seongsu', 'mid', '~5-min walk from Tamburins', 'The biggest Olive Young in Korea — 5 floors, makeup and skincare from every K-beauty brand worth knowing, plus exclusives.', 5, 60, 'https://www.google.com/maps/search/?api=1&query=Olive+Young+N+Seongsu+Seoul'),
                            $this->opt('MUSINSA Megastore Seongsu', 'mid', '~5-min walk from Tamburins', 'For clothes — MUSINSA\'s first megastore, 5 floors of Korean streetwear and contemporary labels plus a food floor, easily half a day on its own.', 10, 150, 'https://www.google.com/maps/search/?api=1&query=Musinsa+Megastore+Seongsu+Seoul'),
                            $this->opt('Fennec Seongsu Flagship', 'splurge', '~8-min walk from Tamburins', 'For bags — a gallery-like store of glossy totes, crossbody bags, and minimalist wallets from the popular Korean accessories label.', 40, 120, 'https://www.google.com/maps/search/?api=1&query=Fennec+Seongsu+Flagship+Seoul'),
                        ],
                    ],
                    [
                        'time' => '12:00', 'title' => 'Taxi back to Ikseon-dong', 'cost_label' => '~$10–13 transport',
                        'description' => '~20–25 min back toward Jongno for lunch.',
                    ],
                    [
                        'time' => '12:30', 'title' => 'Ikseon-dong Hanok Village, lunch', 'cost_label' => '~$8–25 food',
                        'option_label' => 'Lunch options',
                        'options' => [
                            $this->opt('익선취향 (Ikseon Chwihyang), Ikseon-dong', 'mid', 'Jongno, near the friends\' hotel', 'Hanok cafe/restaurant tucked into the Ikseon-dong alleys — lunch and the neighborhood stroll in one stop, a good place to linger with friends before the goodbye.', 15, 20, 'https://www.google.com/maps/search/?api=1&query=%EC%9D%B5%EC%84%A0%EC%B7%A8%ED%96%A5+Ikseon+Chwihyang+Seoul', true),
                            $this->opt('Gwangjang Market', 'budget', '~1 km from Ikseon-dong', 'Bindaetteok and mayak gimbap — a snack round instead of a sit-down lunch, in one of Seoul\'s oldest markets.', 8, 12, 'https://maps.google.com/?cid=15444711502569316164'),
                        ],
                    ],
                    [
                        'time' => '15:30', 'title' => 'Go back to Jongno hotel for luggage', 'cost_label' => '~$5–10 transport',
                        'description' => '~1.3 km, 15-min walk or a quick taxi from Ikseon-dong. Grab your bags from the front desk and say your goodbyes here.',
                        'map_url' => 'https://maps.google.com/?cid=1368431404401387531',
                    ],
                    [
                        'time' => '16:30', 'title' => 'Head to Incheon Airport', 'cost_label' => '~$10–16 transport',
                        'description' => 'Airport Limousine Bus from a nearby Jongno/Gwanghwamun stop — ~52 km, ~60–75 min depending on traffic. Confirm the exact stop and schedule that morning, since it\'s a different pickup point from the COEX bus used on arrival. Direct taxi is faster but pricier (~$45–55).',
                    ],
                    [
                        'time' => '20:30', 'title' => 'Depart — PR403 to Manila',
                        'description' => 'Lands 23:40. Trip complete.',
                    ],
                ],
            ],
        ];
    }

    private function budgetLines(): array
    {
        return [
            ['category' => 'Registration', 'label' => 'Conference / registration fee', 'amount' => 740],
            ['category' => 'Meals', 'label' => 'Per diem', 'note' => '$20 / meal', 'amount' => 300, 'per_person' => true],
            ['category' => 'Accommodation', 'label' => 'Room for 2', 'note' => 'rate ÷ 2 × 4 nights', 'amount' => 320],
            ['category' => 'Insurance', 'label' => 'Travel insurance', 'amount' => 45],
            ['category' => 'Transpo', 'label' => 'PAL airfare', 'amount' => 330],
            ['category' => 'Transpo', 'label' => 'Airport travel tax', 'amount' => 30],
            ['category' => 'Transpo', 'label' => 'In-land transportation', 'note' => '5 days', 'amount' => 100],
            ['category' => 'Communication', 'label' => 'Internet / communication allowance', 'amount' => 15],
        ];
    }
}
