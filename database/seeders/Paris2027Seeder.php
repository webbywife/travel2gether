<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\BuildsSampleTrip;
use Illuminate\Database\Seeder;

/**
 * Public sample: Paris in late spring (19–23 May 2027). Checked Oct 2026:
 * Louvre €32 for non-EU visitors (closed Tuesdays); Orsay €16 with timed
 * booking mandatory since Mar 2026; Sainte-Chapelle €22 non-EEA; Notre-Dame
 * free (optional timed slot); Versailles closed Mondays, Passport ~€35 high
 * season non-EEA; Eiffel summit by lift ~€37; RER B from CDG €14; metro €2.55;
 * Schengen visa €90; EU Entry/Exit System (fingerprints + photo) since 2026.
 */
class Paris2027Seeder extends Seeder
{
    use BuildsSampleTrip;

    public function run(): void
    {
        $this->seedTrip([
            'slug' => 'paris-2027',
            'title' => 'Paris in 5 days',
            'tagline' => 'Sample plan · spring',
            'subhead' => 'Paris when the evenings stay light until ten: Notre-Dame reopened, the Louvre and Orsay booked ahead, a day at Versailles, Montmartre, and the Eiffel Tower sparkling on the hour. Every slot has a cheaper and a fancier option.',
            'destination' => 'Paris, France',
            'origin_label' => 'MNL <-> CDG',
            'start_date' => '2027-05-19',
            'end_date' => '2027-05-23',
            'party_size' => 4,
            'currency' => 'EUR',
            'lat' => 48.8566,
            'lon' => 2.3522,
            'forecast_note' => 'Late May in Paris: ~21 °C by day, ~12 °C at night, a shower some afternoons — and sunset after 21:30, so the evenings are long. Live numbers fill in inside the ~16-day forecast window.',
            'segments' => [
                [
                    'from' => 'MNL', 'to' => 'CDG', 'date' => 'TUE 18 MAY 2027',
                    'depart' => '19:00', 'arrive' => '07:00 +1', 'terminal' => 'NAIA 3 -> CDG (one stop)',
                    'airline' => 'One-stop carrier (e.g. via Doha)', 'flight_no' => '',
                ],
                [
                    'from' => 'CDG', 'to' => 'MNL', 'date' => 'SUN 23 MAY 2027',
                    'depart' => '21:30', 'arrive' => '20:30 +1', 'terminal' => 'CDG -> NAIA 3 (one stop)',
                    'airline' => 'One-stop carrier (e.g. via Doha)', 'flight_no' => '',
                ],
            ],
            'stats' => [
                ['value' => '€90', 'label' => 'Schengen visa'],
                ['value' => '5', 'label' => 'days, 4 nights in the Marais'],
                ['value' => '~21:40', 'label' => 'sunset in late May'],
                ['value' => '€2.55', 'label' => 'a metro ride'],
            ],
        ], $this->days(), $this->budgetLines());
    }

    private function days(): array
    {
        return [
            // ================= DAY 1 =================
            [
                'day_number' => 1,
                'date' => '2027-05-19',
                'title' => 'Land, Notre-Dame, a Seine cruise',
                'summary' => 'Morning arrival, bags at the hotel, then the island where Paris began: Notre-Dame and the stained glass of Sainte-Chapelle. A boat on the Seine at dusk keeps everyone awake.',
                'weather_tag' => 'outdoor',
                'forecast_date' => '2027-05-19',
                'temp_high' => 21,
                'temp_low' => 12,
                'lat' => 48.8530,
                'lon' => 2.3499,
                'weather_note' => 'Mild — a light jacket and a small umbrella.',
                'outfit_chips' => ['light jacket', 'jeans', 'comfortable walking shoes', 'compact umbrella'],
                'area_label' => 'Base: the Marais · Île de la Cité',
                'map_embed_url' => $this->osm(48.8550, 2.3550, 0.02),
                'hiccups' => [
                    'France needs a Schengen visa for a Philippine passport (€90) — apply at the embassy of your main destination, typically up to 6 months and at least 3 weeks before you fly.',
                    'Since 2026 the EU\'s Entry/Exit System takes your fingerprints and photo at your first entry — expect a longer immigration queue the first time.',
                    'Notre-Dame is free; a timed slot (released only a few days ahead on the official site) saves the queue. Sainte-Chapelle is €22 for non-EEA visitors and needs a booked slot.',
                    'Pickpockets work the RER B, the metro and every queue — zip bags, phone not in a back pocket.',
                ],
                'stops' => [
                    [
                        'time' => '07:00', 'title' => 'Land at Charles de Gaulle', 'cost_label' => 'free',
                        'description' => 'Immigration (with the new biometric check), bags, then into town.',
                    ],
                    [
                        'time' => '08:30', 'title' => 'Into Paris', 'cost_label' => '€14–30',
                        'option_label' => 'Getting to the Marais',
                        'options' => [
                            $this->opt('RER B train', 'budget', '~40 min · €14', 'Fast and cheap; crowded at rush hour, and keep an eye on the bags.', 14, 14, true),
                            $this->opt('Taxi — fixed fare to the city', 'mid', '~45–60 min · fixed rate to the Right Bank', 'Split four ways it\'s barely more than the train — and door to door.', 15, 18),
                            $this->opt('Roissybus to Opéra', 'budget', '~60 min', 'A seat and luggage racks, slower in traffic.', 16, 16),
                        ],
                    ],
                    [
                        'time' => '10:00', 'title' => 'Bags at the hotel, coffee and a croissant', 'cost_label' => '~€5–10',
                        'description' => 'The Marais is central, walkable, and close to the islands. Check-in is usually mid-afternoon.',
                    ],
                    [
                        'time' => '11:00', 'title' => 'Notre-Dame de Paris', 'cost_label' => 'free',
                        'description' => 'Reopened in December 2024 after the fire — the restored nave is bright, almost white.',
                    ],
                    [
                        'time' => '13:00', 'title' => 'Sainte-Chapelle', 'cost_label' => '€22',
                        'description' => 'A 13th-century chapel that is mostly stained glass — go on a sunny hour if you can.',
                    ],
                    [
                        'time' => '15:00', 'title' => 'Check in, nap', 'weather_tag' => 'indoor',
                        'description' => 'A short sleep after the overnight flight — set an alarm.',
                    ],
                    [
                        'time' => '19:30', 'title' => 'Your first evening', 'cost_label' => '€0–60',
                        'option_label' => 'Pick one',
                        'options' => [
                            $this->opt('Seine boat cruise', 'mid', '~1 hr from the Eiffel Tower or Pont Neuf', 'All the monuments from the water as the light fades.', 17, 20, true),
                            $this->opt('Picnic on the Île Saint-Louis quay', 'budget', 'supermarket wine, cheese, baguette', 'What Parisians do on a warm evening.', 8, 15),
                            $this->opt('Dinner cruise', 'splurge', 'book ahead', 'Dinner on the boat — the romantic splurge.', 80, 120),
                        ],
                    ],
                ],
            ],

            // ================= DAY 2 =================
            [
                'day_number' => 2,
                'date' => '2027-05-20',
                'title' => 'The Louvre, the gardens, the Eiffel Tower lit up',
                'summary' => 'The Louvre at opening, the Tuileries, up the Champs-Élysées to the Arc de Triomphe, and the tower sparkling after dark.',
                'weather_tag' => 'covered',
                'forecast_date' => '2027-05-20',
                'temp_high' => 21,
                'temp_low' => 12,
                'lat' => 48.8606,
                'lon' => 2.3376,
                'weather_note' => 'Indoors in the morning, a long walk in the afternoon.',
                'outfit_chips' => ['layers', 'comfortable shoes — miles of museum floor', 'small cross-body bag'],
                'area_label' => 'Louvre → Tuileries → Champs-Élysées → Trocadéro',
                'map_embed_url' => $this->osm(48.8650, 2.3150, 0.035),
                'hiccups' => [
                    'The Louvre is €32 for non-EU visitors and closed on Tuesdays. Book a timed slot online — the Mona Lisa room is the bottleneck, so go there first or last.',
                    'The Eiffel Tower sparkles for five minutes on the hour after dark — in late May that\'s not until ~22:00.',
                    'Rain swap: the covered passages (Galerie Vivienne, Passage des Panoramas) instead of the Tuileries.',
                ],
                'stops' => [
                    [
                        'time' => '09:00', 'title' => 'The Louvre', 'cost_label' => '€32',
                        'option_label' => 'How to do it',
                        'options' => [
                            $this->opt('Highlights route, ~3 hours', 'mid', 'timed ticket', 'Mona Lisa, Venus de Milo, Winged Victory, the Napoleon III apartments. Then leave.', 32, 32, true),
                            $this->opt('Guided tour', 'splurge', '~2 hours, small group', 'The stories behind the highlights — and help with the crowds.', 70, 90),
                            $this->opt('Outside only — the pyramid + Musée de l\'Orangerie', 'budget', 'Monet\'s Water Lilies', 'The Orangerie\'s two oval rooms of water lilies are a calmer, cheaper Louvre swap.', 13, 15),
                        ],
                    ],
                    [
                        'time' => '12:30', 'title' => 'Lunch + the Tuileries', 'cost_label' => '~€10–25 food',
                        'description' => 'A sandwich from a boulangerie on a metal chair by the fountain.',
                    ],
                    [
                        'time' => '14:00', 'title' => 'Champs-Élysées to the Arc de Triomphe', 'cost_label' => 'free (rooftop ticketed)',
                        'description' => 'Walk up the avenue, or take the metro if feet are done. The Arc\'s rooftop has the view down the twelve avenues.',
                    ],
                    [
                        'time' => '17:00', 'title' => 'Rest at the hotel', 'weather_tag' => 'indoor',
                    ],
                    [
                        'time' => '19:30', 'title' => 'Dinner near the tower', 'cost_label' => '~€15–45 food',
                        'description' => 'A bistro in the 7th, or crêpes to carry to the Champ de Mars.',
                    ],
                    [
                        'time' => '21:45', 'title' => 'The Eiffel Tower from Trocadéro', 'cost_label' => 'free',
                        'description' => 'The classic view across the river. Stay for the 22:00 sparkle.',
                    ],
                ],
            ],

            // ================= DAY 3 =================
            [
                'day_number' => 3,
                'date' => '2027-05-21',
                'title' => 'Versailles',
                'summary' => 'The palace, the Hall of Mirrors and the gardens — a full day, 40 minutes out of the city.',
                'weather_tag' => 'outdoor',
                'forecast_date' => '2027-05-21',
                'temp_high' => 22,
                'temp_low' => 11,
                'lat' => 48.8049,
                'lon' => 2.1204,
                'weather_note' => 'The gardens are huge and open — sunscreen, water, and a hat.',
                'outfit_chips' => ['comfortable shoes', 'sun hat', 'light layer', 'water bottle'],
                'area_label' => 'Château de Versailles',
                'map_embed_url' => $this->osm(48.8049, 2.1204, 0.02),
                'hiccups' => [
                    'Versailles is closed on Mondays, and Tuesdays are the most crowded (the Louvre is shut). A Friday is a good choice.',
                    'Book a timed palace entry — the "Passport" ticket (~€35 in high season for non-EEA visitors) covers the palace, the Trianon estates and the gardens.',
                    'The gardens are bigger than they look: the Trianons are ~25 minutes on foot from the palace — rent a golf cart or bikes.',
                ],
                'stops' => [
                    [
                        'time' => '08:00', 'title' => 'RER C to Versailles Château Rive Gauche', 'cost_label' => '~€4',
                        'description' => '~40 minutes, then a 10-minute walk to the gates.',
                    ],
                    [
                        'time' => '09:00', 'title' => 'The palace', 'cost_label' => '~€35',
                        'description' => 'The State Apartments and the Hall of Mirrors — at opening, before the tour groups arrive.',
                    ],
                    [
                        'time' => '12:00', 'title' => 'Lunch in the gardens', 'cost_label' => '~€15–35 food',
                        'description' => 'A café by the Grand Canal, or a picnic on the grass outside the formal parterres.',
                    ],
                    [
                        'time' => '13:30', 'title' => 'The estate', 'cost_label' => '€0–40',
                        'option_label' => 'How to see it',
                        'options' => [
                            $this->opt('Bikes along the Grand Canal', 'budget', 'hourly rental', 'The easiest way to reach the far end of the estate.', 8, 12, true),
                            $this->opt('Golf cart', 'mid', 'up to 4 people', 'One cart for the group — the Trianons and Marie-Antoinette\'s hamlet without the walk.', 10, 12),
                            $this->opt('Rowing boat on the Grand Canal', 'mid', 'by the half hour', 'A lazy hour on the water.', 5, 8),
                        ],
                    ],
                    [
                        'time' => '17:30', 'title' => 'Back to Paris', 'cost_label' => '~€4',
                    ],
                    [
                        'time' => '20:00', 'title' => 'Dinner in the Marais', 'cost_label' => '~€10–40 food',
                        'description' => 'Falafel on Rue des Rosiers is the cheap legend; a bistro on Place du Marché-Sainte-Catherine is the sit-down.',
                    ],
                ],
            ],

            // ================= DAY 4 =================
            [
                'day_number' => 4,
                'date' => '2027-05-22',
                'title' => 'Montmartre, Orsay, up the Eiffel Tower',
                'summary' => 'The painters\' hill and Sacré-Cœur in the morning, the Impressionists at Orsay, and the tower itself at sunset.',
                'weather_tag' => 'outdoor',
                'forecast_date' => '2027-05-22',
                'temp_high' => 21,
                'temp_low' => 12,
                'lat' => 48.8867,
                'lon' => 2.3431,
                'weather_note' => 'Montmartre is steep — the funicular takes a metro ticket.',
                'outfit_chips' => ['layers', 'walking shoes', 'a warmer layer for the tower top'],
                'area_label' => 'Montmartre → Musée d\'Orsay → Eiffel Tower',
                'map_embed_url' => $this->osm(48.8700, 2.3200, 0.04),
                'hiccups' => [
                    'Musée d\'Orsay needs a booked timed entry since 2026 (€16) — no walk-ins.',
                    'At Sacré-Cœur, ignore people offering "friendship bracelets" — they tie one on and demand money.',
                    'Eiffel Tower tickets go on sale about 60 days ahead and sunset slots go first; the summit by lift is ~€37, the 2nd floor by stairs much less.',
                ],
                'stops' => [
                    [
                        'time' => '09:00', 'title' => 'Montmartre + Sacré-Cœur', 'cost_label' => 'free',
                        'description' => 'The white basilica and the view from its steps, Place du Tertre\'s painters, and the quiet lanes behind it.',
                    ],
                    [
                        'time' => '12:00', 'title' => 'Lunch on the hill', 'cost_label' => '~€15–30 food',
                        'description' => 'A croque-monsieur in a café on Rue des Abbesses.',
                    ],
                    [
                        'time' => '14:00', 'title' => 'Musée d\'Orsay', 'cost_label' => '€16',
                        'description' => 'Van Gogh, Monet and Renoir in an old railway station — and the view through the giant clock window.',
                        'weather_tag' => 'indoor',
                    ],
                    [
                        'time' => '19:00', 'title' => 'Up the Eiffel Tower', 'cost_label' => '€15–37',
                        'option_label' => 'How high',
                        'options' => [
                            $this->opt('Summit by lift', 'splurge', '~€37', 'The very top, as the sun goes down.', 37, 37, true),
                            $this->opt('2nd floor by lift', 'mid', 'the best view of the city', 'Many say the 2nd floor view is better than the top — the buildings are still close.', 24, 24),
                            $this->opt('2nd floor by stairs', 'budget', '674 steps', 'The cheapest way up, and a story to tell.', 15, 15),
                        ],
                    ],
                    [
                        'time' => '21:30', 'title' => 'Dinner on the Left Bank', 'cost_label' => '~€15–40 food',
                    ],
                ],
            ],

            // ================= DAY 5 =================
            [
                'day_number' => 5,
                'date' => '2027-05-23',
                'title' => 'Sunday market, last shopping, fly home',
                'summary' => 'A Paris Sunday: a market breakfast, the view from the Galeries Lafayette rooftop, and an evening flight.',
                'weather_tag' => 'covered',
                'forecast_date' => '2027-05-23',
                'temp_high' => 21,
                'temp_low' => 12,
                'lat' => 48.8738,
                'lon' => 2.3320,
                'weather_note' => 'Many shops close on Sundays — the department stores near Opéra open.',
                'outfit_chips' => ['comfy travel outfit', 'light jacket'],
                'area_label' => 'Marais → Opéra → CDG',
                'map_embed_url' => $this->osm(48.8650, 2.3450, 0.03),
                'hiccups' => [
                    'Leave for CDG by ~17:30 for a 21:30 flight; the EU exit check is biometric now too.',
                    'Tax-free (détaxe): spend over the minimum in one store on one day, get the form, and validate it at the PABLO kiosks at CDG before check-in.',
                ],
                'stops' => [
                    [
                        'time' => '09:00', 'title' => 'Checkout — bags at the hotel',
                    ],
                    [
                        'time' => '09:30', 'title' => 'Market breakfast', 'cost_label' => '~€8–20 food',
                        'description' => 'Marché des Enfants Rouges, the oldest covered market in Paris, is a few streets away.',
                    ],
                    [
                        'time' => '11:30', 'title' => 'Last shopping', 'cost_label' => '~€30–300',
                        'option_label' => 'Where to shop',
                        'options' => [
                            $this->opt('Galeries Lafayette + the free rooftop', 'mid', 'Opéra', 'The stained-glass dome, then the free rooftop terrace view.', 50, 200, true),
                            $this->opt('Supermarket pasalubong', 'budget', 'Monoprix', 'Chocolate, biscuits, mustard, salted-butter caramels — the best value.', 30, 60),
                            $this->opt('Ladurée or Pierre Hermé macarons', 'splurge', 'boxed to travel', 'The gift box everyone expects from Paris.', 40, 80),
                        ],
                    ],
                    [
                        'time' => '17:30', 'title' => 'Collect bags → CDG', 'cost_label' => '€14–18',
                    ],
                    [
                        'time' => '21:30', 'title' => 'Fly home to Manila',
                        'description' => 'One stop. Trip complete.',
                    ],
                ],
            ],
        ];
    }

    private function budgetLines(): array
    {
        return [
            ['category' => 'Transpo', 'label' => 'Round-trip airfare', 'note' => 'MNL ⇄ CDG, one stop', 'amount' => 1000],
            ['category' => 'Transpo', 'label' => 'PH travel tax + terminal fee', 'amount' => 30],
            ['category' => 'Transpo', 'label' => 'Metro, RER and Versailles train', 'amount' => 60],
            ['category' => 'Accommodation', 'label' => 'Marais hotel', 'note' => 'quad or 2 twins ÷ 4 × 4 nights', 'amount' => 360],
            ['category' => 'Meals', 'label' => 'Per diem', 'note' => '~€18 / meal × 3 × 5 days', 'amount' => 270, 'per_person' => true],
            ['category' => 'Rail & entry', 'label' => 'Louvre, Orsay, Sainte-Chapelle, Versailles, Eiffel Tower', 'amount' => 140],
            ['category' => 'Rail & entry', 'label' => 'Seine cruise', 'amount' => 18],
            ['category' => 'Shopping', 'label' => 'Pasalubong', 'note' => 'chocolate, macarons, souvenirs', 'amount' => 80],
            ['category' => 'Insurance', 'label' => 'Travel insurance', 'note' => 'required for the Schengen visa', 'amount' => 40],
            ['category' => 'Communication', 'label' => 'eSIM', 'note' => '~5 days, EU-wide', 'amount' => 12],
            ['category' => 'Visa', 'label' => 'Schengen visa', 'note' => 'plus the visa centre\'s service fee', 'amount' => 90],
        ];
    }
}
