<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\BuildsSampleTrip;
use Illuminate\Database\Seeder;

/**
 * Public sample: a first-timer's 4 days in Singapore from Manila (visa-free
 * for a Philippine passport). Hours, show times and fees checked Oct 2026:
 * Garden Rhapsody 19:45/20:45; Spectra 20:00/21:00 (+22:00 Fri–Sat); SG
 * Arrival Card within 3 days before arrival; Sentosa Express S$4 on entry;
 * Satay Street from 19:00 on weekdays; National Orchid Garden S$15.
 */
class Singapore2027Seeder extends Seeder
{
    use BuildsSampleTrip;

    public function run(): void
    {
        $this->seedTrip([
            'slug' => 'singapore-2027',
            'title' => 'Singapore in 4 days',
            'tagline' => 'Sample plan',
            'subhead' => 'A first-timer\'s Singapore from Manila — no visa needed. Marina Bay\'s free light shows, a Sentosa day, the Botanic Gardens and three neighbourhoods in one afternoon, with hawker food at every meal. Tap a different option in any slot to make it yours.',
            'destination' => 'Singapore, Singapore',
            'origin_label' => 'MNL <-> SIN',
            'start_date' => '2027-03-11',
            'end_date' => '2027-03-14',
            'party_size' => 4,
            'currency' => 'SGD',
            'lat' => 1.2838,   // Chinatown
            'lon' => 103.8443,
            'forecast_note' => 'March is one of Singapore\'s drier months, but "dry" here still means ~32 °C, humid, and a short, heavy afternoon thunderstorm on some days. Every day below has an indoor swap. Live numbers fill in once the dates are inside the ~16-day forecast window.',
            'segments' => [
                [
                    'from' => 'MNL', 'to' => 'SIN', 'date' => 'THU 11 MAR 2027',
                    'depart' => '08:05', 'arrive' => '11:50', 'terminal' => 'NAIA 3 -> Changi',
                    'airline' => 'Cebu Pacific', 'flight_no' => '',
                ],
                [
                    'from' => 'SIN', 'to' => 'MNL', 'date' => 'SUN 14 MAR 2027',
                    'depart' => '21:00', 'arrive' => '00:45 +1', 'terminal' => 'Changi -> NAIA 3',
                    'airline' => 'Cebu Pacific', 'flight_no' => '',
                ],
            ],
            'stats' => [
                ['value' => '0', 'label' => 'visas needed (PH passport)'],
                ['value' => '4', 'label' => 'days, 3 nights in Chinatown'],
                ['value' => '~3h45', 'label' => 'flight each way'],
                ['value' => '2', 'label' => 'free light shows a night'],
            ],
        ], $this->days(), $this->budgetLines());
    }

    private function days(): array
    {
        return [
            // ================= DAY 1 =================
            [
                'day_number' => 1,
                'date' => '2027-03-11',
                'title' => 'Land, Chinatown, Marina Bay lights',
                'summary' => 'Land before noon, see the indoor waterfall without leaving the airport, drop bags in Chinatown, eat chicken rice for dinner, and end at Marina Bay for both free light shows.',
                'weather_tag' => 'outdoor',
                'forecast_date' => '2027-03-11',
                'temp_high' => 32,
                'temp_low' => 25,
                'lat' => 1.2838,
                'lon' => 103.8443,
                'weather_note' => 'Hot and humid with a chance of a short afternoon storm. The afternoon is mostly indoors or under cover; the evening is outdoors when it\'s cooler.',
                'outfit_chips' => ['light breathable top', 'shorts or a light skirt', 'comfy sandals or sneakers', 'compact umbrella', 'a layer for freezing malls and MRT'],
                'area_label' => 'Base: Chinatown — the MRT is at the door',
                'map_embed_url' => $this->osm(1.2838, 103.8443, 0.025),
                'hiccups' => [
                    'Submit the SG Arrival Card online (ica.gov.sg) within the 3 days before you land — no card, no smooth immigration.',
                    'Tapping a foreign Visa/Mastercard on the MRT works, but it adds a small admin fee (about S$0.60 per day of travel). A local EZ-Link card avoids it if you\'re riding a lot.',
                    'Jewel\'s Rain Vortex starts at 11:00 on weekdays (10:00 on weekends) — the waterfall runs all afternoon; the light shows are only at night.',
                    'Buddha Tooth Relic Temple wants shoulders and knees covered; shawls are provided at the door if you forget.',
                    'Tian Tian at Maxwell is closed on Mondays and the queue peaks at 12:00–13:30 and after 18:00 — aim for ~17:30.',
                    'You can\'t catch Garden Rhapsody at 19:45 and Spectra at 20:00. Do Rhapsody at 19:45, then walk ~15 min to the Event Plaza for Spectra at 21:00.',
                ],
                'stops' => [
                    [
                        'time' => '11:50', 'title' => 'Land at Changi', 'cost_label' => 'free',
                        'description' => 'Immigration is mostly automated gates once your SG Arrival Card is in. Use the airport\'s baggage storage if you want to explore Jewel hands-free.',
                    ],
                    [
                        'time' => '12:45', 'title' => 'Jewel — the Rain Vortex', 'cost_label' => 'free',
                        'description' => 'The 40-metre indoor waterfall under the glass dome, linked to Terminals 1–3 by walkway — free to see, no boarding pass needed. Grab a first lunch in the food court here.',
                        'weather_tag' => 'indoor',
                    ],
                    [
                        'time' => '14:00', 'title' => 'Into the city', 'cost_label' => '~S$2–30 transport',
                        'option_label' => 'Getting to Chinatown',
                        'options' => [
                            $this->opt('MRT (East-West → Downtown/Thomson-East Coast)', 'budget', '~1 hr, one or two changes · ~S$2 a person', 'Tap your card at the gate. Easy with a carry-on; a pain with four big suitcases.', 2, 3, true),
                            $this->opt('Grab / taxi, split four ways', 'mid', '~25–30 min · ~S$25–35 a car', 'For a group of four with luggage it\'s barely more than the train each.', 7, 9),
                            $this->opt('Hotel shuttle or private transfer', 'splurge', 'book ahead', 'Only worth it if someone in the group needs door-to-door.', 15, 25),
                        ],
                    ],
                    [
                        'time' => '15:00', 'title' => 'Check in — Chinatown', 'cost_label' => 'see budget',
                        'description' => 'Chinatown sits on two MRT lines and has hawker food on every corner, which is why it\'s the base. Shower and cool down — the heat is the real schedule-maker here.',
                        'weather_tag' => 'indoor',
                    ],
                    [
                        'time' => '16:00', 'title' => 'Buddha Tooth Relic Temple + Chinatown streets', 'cost_label' => 'free',
                        'description' => 'A towering Tang-style temple with a rooftop orchid garden, free to enter (it closes around 17:00, so go first). Then wander Pagoda and Trengganu Streets for snacks and pasalubong.',
                        'weather_tag' => 'covered',
                    ],
                    [
                        'time' => '17:30', 'title' => 'Dinner at Maxwell Food Centre', 'cost_label' => '~S$5–12 food',
                        'option_label' => 'What to eat',
                        'options' => [
                            $this->opt('Tian Tian Hainanese Chicken Rice', 'budget', 'stall #01-10/11 · closed Mondays', 'The famous one — go before the after-work queue. Zhen Zhen porridge a few stalls down if chicken rice isn\'t your thing.', 5, 8, true),
                            $this->opt('Graze the whole centre', 'budget', 'split 4–5 dishes for the table', 'Fried carrot cake, popiah, sugarcane juice — order from different stalls and share.', 6, 10),
                            $this->opt('A sit-down Chinatown restaurant', 'mid', 'air-conditioned', 'If the group needs a break from the heat after a travel day.', 20, 35),
                        ],
                    ],
                    [
                        'time' => '19:45', 'title' => 'Garden Rhapsody at the Supertree Grove', 'cost_label' => 'free',
                        'description' => 'The light-and-music show in the Supertrees at Gardens by the Bay, ~15 minutes. Lie on the ground under the trees — everyone does. Second show at 20:45 if you\'re late.',
                    ],
                    [
                        'time' => '21:00', 'title' => 'Spectra on the Marina Bay Sands waterfront', 'cost_label' => 'free',
                        'description' => 'Lasers, water screens and music over the bay, ~15 minutes, from the Event Plaza in front of the MBS shoppes. Then the waterfront walk back to Bayfront MRT.',
                    ],
                ],
            ],

            // ================= DAY 2 =================
            [
                'day_number' => 2,
                'date' => '2027-03-12',
                'title' => 'Sentosa day',
                'summary' => 'One island, three kinds of day: the theme park, the aquarium, or free beaches. Back to the city for satay under the stars and the late Friday light show.',
                'weather_tag' => 'outdoor',
                'forecast_date' => '2027-03-12',
                'temp_high' => 32,
                'temp_low' => 25,
                'lat' => 1.2494,
                'lon' => 103.8303,
                'weather_note' => 'Sun and heat on an island with little shade. Sunscreen, water, and an indoor fallback for the storm hour.',
                'outfit_chips' => ['swimwear under your clothes', 'quick-dry shorts', 'sandals', 'sunscreen + cap', 'a dry tee for the ride home'],
                'area_label' => 'Sentosa — via HarbourFront / VivoCity',
                'map_embed_url' => $this->osm(1.2494, 103.8303, 0.03),
                'hiccups' => [
                    'The Sentosa Express charges S$4 when you board at VivoCity; rides on the island and back out are free.',
                    'Universal Studios tickets are cheaper online and the park is busiest on weekends and school holidays — a Friday is a good choice.',
                    'Afternoon thunderstorms pause outdoor rides and empty the beaches. S.E.A. Aquarium is the indoor swap on the same island.',
                    'Satay Street at Lau Pa Sat only starts at 19:00 on weekdays — arrive earlier and you\'ll just find a normal road.',
                ],
                'stops' => [
                    [
                        'time' => '09:30', 'title' => 'MRT to HarbourFront → Sentosa Express', 'cost_label' => 'S$4 + MRT',
                        'description' => 'Through VivoCity mall to the Sentosa Express on level 3. Everything on the island is a few stops apart.',
                    ],
                    [
                        'time' => '10:00', 'title' => 'Your Sentosa day', 'cost_label' => 'free–S$90',
                        'option_label' => 'Pick the day',
                        'options' => [
                            $this->opt('Universal Studios Singapore', 'splurge', 'opens ~10:00 · from ~S$80+, buy online', 'The whole day, rides first before the queues build. Lunch inside the park.', 80, 90, true),
                            $this->opt('S.E.A. Aquarium + beach afternoon', 'mid', 'indoors, ~2 hours', 'The giant ocean-gallery window, then Siloso or Palawan Beach. The best rainy-day pick.', 45, 50),
                            $this->opt('Free beaches + Fort Siloso', 'budget', 'free', 'Palawan Beach\'s suspension bridge to the "southernmost point of continental Asia" viewpoint, then the old fort\'s tunnels. Bring snacks.', 0, 10),
                        ],
                    ],
                    [
                        'time' => '17:00', 'title' => 'Back to the hotel — shower, rest', 'cost_label' => 'MRT',
                        'description' => 'Sentosa Express back to VivoCity (free), MRT to Chinatown.',
                        'weather_tag' => 'indoor',
                    ],
                    [
                        'time' => '19:30', 'title' => 'Satay Street at Lau Pa Sat', 'cost_label' => '~S$10–20 food',
                        'description' => 'Boon Tat Street closes to traffic and fills with satay grills from 19:00. Order by the stick (chicken, mutton, prawn) and add a jug of sugarcane juice. Inside the Victorian market hall there\'s everything else.',
                        'option_label' => 'How to eat it',
                        'options' => [
                            $this->opt('Satay sets for the table', 'budget', 'order 10 sticks a head', 'Shared and cheap. Stalls 7 and 8 have the longest queues for a reason.', 10, 15, true),
                            $this->opt('Satay + seafood (chilli crab, BBQ stingray)', 'mid', 'from the hall\'s seafood stalls', 'The cheaper way to try chilli crab without a restaurant bill.', 20, 35),
                            $this->opt('Skip it — Chinatown Complex food centre', 'budget', 'near the hotel', 'If everyone\'s wiped out after Sentosa.', 5, 10),
                        ],
                    ],
                    [
                        'time' => '22:00', 'title' => 'Late Spectra (Fridays and Saturdays only)', 'cost_label' => 'free',
                        'description' => 'Missed a show last night? There\'s an extra one at 22:00 on Friday and Saturday — about a 20-minute walk from Lau Pa Sat along the bay.',
                    ],
                ],
            ],

            // ================= DAY 3 =================
            [
                'day_number' => 3,
                'date' => '2027-03-13',
                'title' => 'Gardens, then three neighbourhoods',
                'summary' => 'The Botanic Gardens in the cool morning, then Little India and Kampong Glam in one walk — temples, a mosque, murals and murtabak — and a choose-your-evening.',
                'weather_tag' => 'outdoor',
                'forecast_date' => '2027-03-13',
                'temp_high' => 32,
                'temp_low' => 25,
                'lat' => 1.3057,
                'lon' => 103.8542,
                'weather_note' => 'Do the gardens before 11:00. The afternoon walk has lots of covered five-foot ways and air-conditioned shops to duck into.',
                'outfit_chips' => ['top that covers shoulders', 'long skirt or trousers for the mosque and temple', 'walking shoes', 'umbrella'],
                'area_label' => 'Botanic Gardens → Little India → Kampong Glam',
                'map_embed_url' => $this->osm(1.3057, 103.8542, 0.03),
                'hiccups' => [
                    'Sultan Mosque welcomes visitors Saturday to Thursday about 10:00–12:00 and 14:00–16:00 (not Fridays) — cover shoulders and knees; robes are lent at the entrance.',
                    'The National Orchid Garden (S$15 for non-residents) has last entry at 18:00 — the rest of the Botanic Gardens is free from 05:00 to midnight.',
                    'Haji Lane\'s shops open late morning; the murals and cafés are best mid-afternoon.',
                    'Rain swap: the National Gallery or ArtScience Museum instead of the gardens, then the neighbourhoods under the covered walkways.',
                ],
                'stops' => [
                    [
                        'time' => '08:00', 'title' => 'Singapore Botanic Gardens', 'cost_label' => 'free–S$15',
                        'option_label' => 'How much garden',
                        'options' => [
                            $this->opt('Gardens + National Orchid Garden', 'mid', 'UNESCO site · orchid garden S$15', 'The orchid garden is the highlight — thousands of orchids, including ones named after visiting celebrities. Allow ~2 hours.', 15, 15, true),
                            $this->opt('Free gardens only', 'budget', 'Swan Lake, the rainforest patch, the bandstand', 'A lovely 90-minute loop without the ticket.', 0, 0),
                            $this->opt('Rain swap: National Gallery Singapore', 'mid', 'indoors · ticketed', 'Southeast Asia\'s biggest collection of modern art in the old Supreme Court and City Hall.', 20, 25),
                        ],
                    ],
                    [
                        'time' => '11:00', 'title' => 'Brunch near the gardens', 'cost_label' => '~S$6–25 food',
                        'description' => 'Adam Road Food Centre is a short walk from the gardens\' Bukit Timah gate — nasi lemak is the order. Dempsey Hill cafés if the group wants air-con.',
                    ],
                    [
                        'time' => '13:00', 'title' => 'Little India', 'cost_label' => 'free',
                        'description' => 'MRT to Little India. Tekka Centre\'s wet market and food hall, the colourful Sri Veeramakaliamman Temple on Serangoon Road, and the candy-coloured House of Tan Teng Niah. Mustafa Centre is open 24 hours if anyone needs anything at all.',
                        'weather_tag' => 'covered',
                    ],
                    [
                        'time' => '14:30', 'title' => 'Kampong Glam — Sultan Mosque + Haji Lane', 'cost_label' => 'free',
                        'description' => 'A 15-minute walk south-east. The golden-domed Sultan Mosque (visiting window 14:00–16:00), then Arab Street\'s fabric shops and Haji Lane\'s murals and indie boutiques.',
                    ],
                    [
                        'time' => '17:30', 'title' => 'Early dinner — murtabak opposite the mosque', 'cost_label' => '~S$8–15 food',
                        'description' => 'Stuffed, griddled murtabak and teh tarik on North Bridge Road, a Kampong Glam institution. One murtabak feeds two.',
                    ],
                    [
                        'time' => '19:00', 'title' => 'Your evening', 'cost_label' => 'free–S$45',
                        'option_label' => 'Pick one',
                        'options' => [
                            $this->opt('Flower Dome + Cloud Forest after dark', 'mid', 'open till 21:00 · S$46 both domes (non-resident)', 'The cooled domes at night, then Rhapsody again at 20:45 since you\'re next door. The cloud-forest waterfall is the one everyone photographs.', 46, 46, true),
                            $this->opt('Clarke Quay + Singapore River bumboat', 'mid', '~40-min river cruise', 'Past the Merlion and the old godowns, then a drink by the river.', 25, 30),
                            $this->opt('Orchard Road + Lucky Plaza', 'budget', 'free to wander', 'Malls till 22:00 — and Lucky Plaza for Filipino groceries, remittance counters and a taste of home.', 0, 50),
                        ],
                    ],
                ],
            ],

            // ================= DAY 4 =================
            [
                'day_number' => 4,
                'date' => '2027-03-14',
                'title' => 'Tiong Bahru, last shopping, fly home',
                'summary' => 'Check out, leave the bags, eat breakfast in a 1930s housing estate, shop for pasalubong and be at Changi by 18:00.',
                'weather_tag' => 'covered',
                'forecast_date' => '2027-03-14',
                'temp_high' => 32,
                'temp_low' => 25,
                'lat' => 1.2847,
                'lon' => 103.8310,
                'weather_note' => 'A light day — mostly markets and malls. Keep the afternoon storm hour for shopping indoors.',
                'outfit_chips' => ['comfy travel outfit', 'a layer for the plane', 'sneakers'],
                'area_label' => 'Tiong Bahru → Bugis / Orchard → Changi',
                'map_embed_url' => $this->osm(1.2847, 103.8310, 0.03),
                'hiccups' => [
                    'Ask the hotel to hold your bags after checkout and collect them by ~17:00 — leave Chinatown for Changi no later than 17:30 for a 21:00 flight.',
                    'The MRT to Changi takes about an hour with a change at Tanah Merah; a Grab is ~30 minutes but surges on Sunday evenings.',
                    'Shopping tax refund (GST): only at participating shops, with a minimum spend per shop — claim it at the eTRS kiosks at Changi before check-in.',
                ],
                'stops' => [
                    [
                        'time' => '09:00', 'title' => 'Checkout — bags held at the front desk',
                        'description' => 'Pack the pasalubong space now so you know what fits.',
                    ],
                    [
                        'time' => '09:30', 'title' => 'Breakfast in Tiong Bahru', 'cost_label' => '~S$5–20 food',
                        'option_label' => 'Breakfast',
                        'options' => [
                            $this->opt('Tiong Bahru Market food centre', 'budget', 'upstairs hawker floor', 'Chwee kueh (steamed rice cakes) and kopi with the locals.', 4, 8, true),
                            $this->opt('Kaya toast + soft-boiled eggs', 'budget', 'any traditional kopitiam', 'The classic Singapore breakfast — dip the toast in the eggs.', 4, 6),
                            $this->opt('Tiong Bahru Bakery', 'mid', 'the croissants', 'Then a walk around the curved Art Deco blocks.', 12, 20),
                        ],
                    ],
                    [
                        'time' => '11:30', 'title' => 'Last shopping', 'cost_label' => '~S$30–150',
                        'option_label' => 'Where to shop',
                        'options' => [
                            $this->opt('Bugis Street + Bugis Junction', 'budget', 'cheap souvenirs, snacks, keychains', 'The pasalubong run — three-for-S$10 magnets and kaya jars.', 30, 60, true),
                            $this->opt('Orchard Road malls', 'mid', 'ION, Takashimaya', 'Brands and the food halls in the basements.', 50, 150),
                            $this->opt('Chinatown Point + Chinatown Complex', 'budget', 'near the hotel', 'Bak kwa (sweet barbecued jerky) and tea for the family — no detour needed.', 20, 60),
                        ],
                    ],
                    [
                        'time' => '17:00', 'title' => 'Collect bags → Changi', 'cost_label' => '~S$2–35 transport',
                        'description' => 'MRT if you\'re travelling light, a Grab split four ways if the bags multiplied.',
                    ],
                    [
                        'time' => '18:00', 'title' => 'Changi — check in, then Jewel one last time',
                        'description' => 'Check in first, claim any GST refund, then the Rain Vortex light show at 20:00 if you\'re airside in time. Don\'t cut it fine — Changi is big.',
                        'weather_tag' => 'indoor',
                    ],
                    [
                        'time' => '21:00', 'title' => 'Fly home to Manila',
                        'description' => 'Lands around 00:45. Trip complete.',
                    ],
                ],
            ],
        ];
    }

    private function budgetLines(): array
    {
        return [
            ['category' => 'Transpo', 'label' => 'Round-trip airfare', 'note' => 'MNL ⇄ SIN, budget airline with a bag', 'amount' => 280],
            ['category' => 'Transpo', 'label' => 'PH travel tax + terminal fee', 'amount' => 40],
            ['category' => 'Transpo', 'label' => 'MRT + buses, 4 days', 'note' => 'tap a card, ~S$5 a day', 'amount' => 20],
            ['category' => 'Transpo', 'label' => 'Airport transfers', 'note' => 'Grab split 4 ways, both ways', 'amount' => 18],
            ['category' => 'Accommodation', 'label' => 'Chinatown hotel', 'note' => 'quad room ÷ 4 × 3 nights', 'amount' => 210],
            ['category' => 'Meals', 'label' => 'Per diem', 'note' => 'hawker centres, ~S$10 / meal × 3 × 4 days', 'amount' => 120, 'per_person' => true],
            ['category' => 'Rail & entry', 'label' => 'Day 2 — Sentosa', 'note' => 'Universal Studios (or the aquarium)', 'amount' => 85],
            ['category' => 'Rail & entry', 'label' => 'Day 3 — Orchid Garden + Cloud Forest/Flower Dome', 'amount' => 61],
            ['category' => 'Shopping', 'label' => 'Pasalubong', 'note' => 'kaya, bak kwa, snacks, magnets', 'amount' => 60],
            ['category' => 'Insurance', 'label' => 'Travel insurance', 'amount' => 20],
            ['category' => 'Communication', 'label' => 'eSIM / roaming', 'note' => '~4 days', 'amount' => 10],
        ];
    }
}
