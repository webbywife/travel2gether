<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\BuildsSampleTrip;
use Illuminate\Database\Seeder;

/**
 * Public sample: Rome in October (7–10 Oct 2027). Checked Oct 2026:
 * Colosseum €18 + €2 booking (includes the Forum and Palatine); Vatican
 * Museums €17 + €4 booking, closed Sundays except the last of the month;
 * Trevi Fountain basin €2 (09:00–22:00, since Feb 2026); Pantheon €7 (from
 * Jul 2026); Galleria Borghese €18, booked slots only; Leonardo Express €14,
 * 32 min; Schengen visa €90; EU Entry/Exit System since 2026.
 */
class Rome2027Seeder extends Seeder
{
    use BuildsSampleTrip;

    public function run(): void
    {
        $this->seedTrip([
            'slug' => 'rome-2027',
            'title' => 'Rome in 4 days',
            'tagline' => 'Sample plan · autumn',
            'subhead' => 'Rome in golden October: the Colosseum and the Forum, the Vatican and the Sistine Chapel, the Pantheon and the Trevi Fountain, Trastevere for dinner and gelato every afternoon. Tap any slot to make it your group\'s.',
            'destination' => 'Rome, Italy',
            'origin_label' => 'MNL <-> FCO',
            'start_date' => '2027-10-07',
            'end_date' => '2027-10-10',
            'party_size' => 4,
            'currency' => 'EUR',
            'lat' => 41.8992,   // Piazza Navona area
            'lon' => 12.4731,
            'forecast_note' => 'October in Rome: ~22 °C by day, ~13 °C at night — warm afternoons, cool evenings, and the odd heavy rain shower. Live numbers fill in inside the ~16-day forecast window.',
            'segments' => [
                [
                    'from' => 'MNL', 'to' => 'FCO', 'date' => 'WED 06 OCT 2027',
                    'depart' => '19:00', 'arrive' => '07:00 +1', 'terminal' => 'NAIA 3 -> FCO (one stop)',
                    'airline' => 'One-stop carrier (e.g. via Doha)', 'flight_no' => '',
                ],
                [
                    'from' => 'FCO', 'to' => 'MNL', 'date' => 'SUN 10 OCT 2027',
                    'depart' => '21:30', 'arrive' => '20:30 +1', 'terminal' => 'FCO -> NAIA 3 (one stop)',
                    'airline' => 'One-stop carrier (e.g. via Doha)', 'flight_no' => '',
                ],
            ],
            'stats' => [
                ['value' => '€90', 'label' => 'Schengen visa'],
                ['value' => '4', 'label' => 'days, 3 nights by Piazza Navona'],
                ['value' => '€2', 'label' => 'Trevi Fountain, since 2026'],
                ['value' => '2', 'label' => 'countries (Italy + Vatican City)'],
            ],
        ], $this->days(), $this->budgetLines());
    }

    private function days(): array
    {
        return [
            // ================= DAY 1 =================
            [
                'day_number' => 1,
                'date' => '2027-10-07',
                'title' => 'Land, the Pantheon, Trevi, the Spanish Steps',
                'summary' => 'Morning arrival, bags at the hotel, then the classic centro walk — every famous piazza within 20 minutes of the next.',
                'weather_tag' => 'outdoor',
                'forecast_date' => '2027-10-07',
                'temp_high' => 22,
                'temp_low' => 13,
                'lat' => 41.8986,
                'lon' => 12.4769,
                'weather_note' => 'Warm in the sun, cool in the shade of the narrow streets — a light layer.',
                'outfit_chips' => ['t-shirt + light jacket', 'jeans', 'comfortable shoes for cobblestones', 'scarf to cover shoulders in churches'],
                'area_label' => 'Base: near Piazza Navona',
                'map_embed_url' => $this->osm(41.9010, 12.4800, 0.015),
                'hiccups' => [
                    'Italy needs a Schengen visa for a Philippine passport (€90) — apply at the embassy of your main destination, typically up to 6 months and at least 3 weeks before you fly.',
                    'The EU\'s Entry/Exit System takes your fingerprints and photo on your first entry — expect a longer queue at immigration.',
                    'Trevi Fountain\'s basin area costs €2 from 09:00 to 22:00 (card only at the entrance). Go early or late for fewer people.',
                    'Sitting on the Spanish Steps is banned and fined. Eating there too.',
                    'Churches need shoulders and knees covered — carry a scarf.',
                ],
                'stops' => [
                    [
                        'time' => '07:00', 'title' => 'Land at Fiumicino (FCO)', 'cost_label' => 'free',
                        'description' => 'Immigration with the new biometric check, bags, then the train or a taxi.',
                    ],
                    [
                        'time' => '08:30', 'title' => 'Into Rome', 'cost_label' => '€14–15',
                        'option_label' => 'Getting to the centre',
                        'options' => [
                            $this->opt('Taxi — fixed fare to the historic centre', 'mid', '~45 min · flat fare inside the old walls', 'Split four ways it\'s about the same as the train, door to door. Use the official white taxis.', 14, 15, true),
                            $this->opt('Leonardo Express to Termini', 'budget', '32 min · €14', 'Fast and non-stop, but Termini is a 25-minute bus or taxi from Navona.', 14, 14),
                            $this->opt('Private transfer', 'splurge', 'driver waiting at arrivals', 'If anyone needs a fixed pickup.', 18, 25),
                        ],
                    ],
                    [
                        'time' => '10:00', 'title' => 'Bags at the hotel, cornetto and cappuccino', 'cost_label' => '~€3–5',
                        'description' => 'Drink it standing at the bar like the Romans — sitting down costs more.',
                    ],
                    [
                        'time' => '11:00', 'title' => 'The Pantheon', 'cost_label' => '€7',
                        'description' => 'A 2,000-year-old temple with an open hole in its dome — the rain falls straight in.',
                    ],
                    [
                        'time' => '12:30', 'title' => 'Lunch near Piazza Navona', 'cost_label' => '~€12–25 food',
                        'description' => 'Pizza al taglio (by the slice, by weight) or cacio e pepe at a trattoria a street or two away from the piazza — prices drop when the view does.',
                    ],
                    [
                        'time' => '14:00', 'title' => 'Check in, rest', 'weather_tag' => 'indoor',
                    ],
                    [
                        'time' => '17:00', 'title' => 'Trevi Fountain + the Spanish Steps', 'cost_label' => '€2',
                        'description' => 'Throw a coin over your left shoulder to come back to Rome. Then 10 minutes to the Steps and Via Condotti\'s windows.',
                    ],
                    [
                        'time' => '20:00', 'title' => 'Dinner', 'cost_label' => '~€15–45 food',
                        'option_label' => 'Dinner',
                        'options' => [
                            $this->opt('Roman trattoria', 'mid', 'carbonara, amatriciana, cacio e pepe', 'The four classic Roman pastas — order different ones and share.', 20, 30, true),
                            $this->opt('Pizza + supplì', 'budget', 'fried rice balls with mozzarella', 'Thin, crisp Roman pizza — the cheapest good dinner in town.', 12, 18),
                            $this->opt('Rooftop dinner', 'splurge', 'book ahead', 'Domes and terraces at sunset.', 50, 90),
                        ],
                    ],
                ],
            ],

            // ================= DAY 2 =================
            [
                'day_number' => 2,
                'date' => '2027-10-08',
                'title' => 'The Colosseum, the Forum, Trastevere',
                'summary' => 'Ancient Rome in one ticket — the Colosseum, the Roman Forum and the Palatine Hill — then across the river to Trastevere for the evening.',
                'weather_tag' => 'outdoor',
                'forecast_date' => '2027-10-08',
                'temp_high' => 22,
                'temp_low' => 13,
                'lat' => 41.8902,
                'lon' => 12.4922,
                'weather_note' => 'Little shade in the Forum — water and a hat, even in October.',
                'outfit_chips' => ['sun hat', 'water bottle', 'walking shoes with grip (old stones are slippery)'],
                'area_label' => 'Colosseum → Forum → Palatine → Trastevere',
                'map_embed_url' => $this->osm(41.8900, 12.4850, 0.02),
                'hiccups' => [
                    'Colosseum tickets are €18 + a €2 booking fee, named to each person and sold for a timed slot — they sell out weeks ahead; bring the passport that matches the name.',
                    'The ticket includes the Roman Forum and the Palatine Hill — don\'t buy them separately.',
                    'Ignore people dressed as gladiators near the Colosseum — photos with them aren\'t free.',
                    'Rain swap: the Capitoline Museums (indoors, overlooking the Forum).',
                ],
                'stops' => [
                    [
                        'time' => '08:30', 'title' => 'The Colosseum', 'cost_label' => '€20+',
                        'option_label' => 'Which ticket',
                        'options' => [
                            $this->opt('Standard timed entry', 'mid', '€18 + €2 · includes the Forum', 'The main levels of the arena — about an hour inside.', 20, 20, true),
                            $this->opt('With the arena floor or underground', 'splurge', 'guided, limited places', 'Stand where the gladiators did, and see the tunnels below.', 30, 60),
                            $this->opt('Outside only + the Forum', 'budget', 'the view from Via dei Fori Imperiali', 'If tickets are sold out, the outside is free and spectacular.', 0, 20),
                        ],
                    ],
                    [
                        'time' => '10:00', 'title' => 'Roman Forum + Palatine Hill', 'cost_label' => 'included',
                        'description' => 'The ruins of the city\'s centre — temples, the Senate house, the Via Sacra. Then up the Palatine for the view back over it all.',
                    ],
                    [
                        'time' => '13:00', 'title' => 'Lunch in Monti', 'cost_label' => '~€12–25 food',
                        'description' => 'Rome\'s oldest neighbourhood, five minutes from the Colosseum — small trattorias and a lively square.',
                    ],
                    [
                        'time' => '15:00', 'title' => 'Altare della Patria + Piazza Venezia', 'cost_label' => 'free',
                        'description' => 'The huge white monument the Romans call "the wedding cake" — the terraces are free; the glass lift to the top is paid.',
                    ],
                    [
                        'time' => '17:30', 'title' => 'Gelato, then cross to Trastevere', 'cost_label' => '~€3–5',
                        'description' => 'Pick a gelateria where the pistachio is brownish-green, not bright — that\'s the real thing.',
                    ],
                    [
                        'time' => '19:30', 'title' => 'Dinner in Trastevere', 'cost_label' => '~€15–35 food',
                        'description' => 'Ivy-covered lanes, the mosaics of Santa Maria in Trastevere lit at night, and trattorias spilling onto the cobbles.',
                    ],
                ],
            ],

            // ================= DAY 3 =================
            [
                'day_number' => 3,
                'date' => '2027-10-09',
                'title' => 'The Vatican — museums, the Sistine Chapel, St Peter\'s',
                'summary' => 'A second country before lunch: the Vatican Museums and Michelangelo\'s ceiling, then St Peter\'s Basilica and its dome. The afternoon is yours.',
                'weather_tag' => 'covered',
                'forecast_date' => '2027-10-09',
                'temp_high' => 22,
                'temp_low' => 13,
                'lat' => 41.9065,
                'lon' => 12.4536,
                'weather_note' => 'Mostly indoors — a good day if it rains.',
                'outfit_chips' => ['shoulders and knees covered (enforced)', 'comfortable shoes', 'small bag (big bags must be checked)'],
                'area_label' => 'Vatican City',
                'map_embed_url' => $this->osm(41.9050, 12.4560, 0.012),
                'hiccups' => [
                    'Vatican Museums are €17 + €4 booking, open Monday–Saturday and closed on Sundays (except the last Sunday of the month). Saturdays are busy — book the first slot.',
                    'The dress code (covered shoulders and knees) is enforced at both the museums and St Peter\'s — you\'ll be turned away.',
                    'No photos in the Sistine Chapel, and guards ask for silence.',
                    'Galleria Borghese only sells timed slots (€18, every two hours) — book weeks ahead or skip it.',
                ],
                'stops' => [
                    [
                        'time' => '08:30', 'title' => 'Vatican Museums + the Sistine Chapel', 'cost_label' => '€21+',
                        'option_label' => 'How to do it',
                        'options' => [
                            $this->opt('Booked entry, self-guided', 'mid', '€17 + €4 · ~3 hours', 'The Raphael Rooms, the Gallery of Maps, then the Sistine Chapel at the end.', 21, 21, true),
                            $this->opt('Early-access guided tour', 'splurge', 'in before the doors open', 'The Sistine Chapel with fewer people — the one splurge that really changes the visit.', 70, 100),
                            $this->opt('Skip the museums — St Peter\'s only', 'budget', 'the basilica is free', 'Michelangelo\'s Pietà and the dome, without the museum ticket.', 0, 0),
                        ],
                    ],
                    [
                        'time' => '12:00', 'title' => 'St Peter\'s Basilica', 'cost_label' => 'free (dome extra)',
                        'description' => 'The biggest church in the world: the Pietà, Bernini\'s canopy, and — for those with legs left — the climb up the dome for the view over the square.',
                    ],
                    [
                        'time' => '14:00', 'title' => 'Lunch in Prati', 'cost_label' => '~€12–25 food',
                        'description' => 'The neighbourhood beside the Vatican, cheaper than the streets right at its gates.',
                    ],
                    [
                        'time' => '15:30', 'title' => 'Your afternoon', 'cost_label' => '€0–18',
                        'option_label' => 'Pick one',
                        'options' => [
                            $this->opt('Castel Sant\'Angelo + the bridge of angels', 'mid', 'the papal fortress', 'Up through the castle to the terrace, then across Ponte Sant\'Angelo\'s statues.', 16, 18, true),
                            $this->opt('Galleria Borghese', 'splurge', '€18, timed slot · booked ahead', 'Bernini\'s marble sculptures — some of the most beautiful rooms in Rome.', 18, 18),
                            $this->opt('Villa Borghese park', 'budget', 'free', 'Rome\'s big park and the Pincio terrace view at sunset.', 0, 0),
                        ],
                    ],
                    [
                        'time' => '20:00', 'title' => 'Dinner + Piazza Navona at night', 'cost_label' => '~€15–35 food',
                        'description' => 'Bernini\'s Fountain of the Four Rivers lit up, street painters and musicians.',
                    ],
                ],
            ],

            // ================= DAY 4 =================
            [
                'day_number' => 4,
                'date' => '2027-10-10',
                'title' => 'A Roman Sunday, then home',
                'summary' => 'Sunday in Rome: the flea market at Porta Portese or the Pope\'s Angelus at noon, a long lunch, and an evening flight.',
                'weather_tag' => 'outdoor',
                'forecast_date' => '2027-10-10',
                'temp_high' => 22,
                'temp_low' => 13,
                'lat' => 41.8930,
                'lon' => 12.4740,
                'weather_note' => 'Many shops close or open late on Sundays; the markets and churches don\'t.',
                'outfit_chips' => ['comfy travel outfit', 'scarf for St Peter\'s Square'],
                'area_label' => 'Trastevere / St Peter\'s → FCO',
                'map_embed_url' => $this->osm(41.8930, 12.4700, 0.02),
                'hiccups' => [
                    'Campo de\' Fiori\'s market runs Monday to Saturday — on Sundays it\'s just a square. Porta Portese is the Sunday market.',
                    'The Angelus at noon in St Peter\'s Square happens most Sundays when the Pope is in Rome — check the Vatican\'s calendar.',
                    'Leave for FCO by ~17:30 for a 21:30 flight; Rome\'s city tax is paid at the hotel, per person per night.',
                ],
                'stops' => [
                    [
                        'time' => '09:00', 'title' => 'Checkout — bags at the hotel',
                    ],
                    [
                        'time' => '09:30', 'title' => 'Sunday morning', 'cost_label' => 'free',
                        'option_label' => 'Pick one',
                        'options' => [
                            $this->opt('Porta Portese flea market', 'budget', 'Trastevere · Sunday mornings', 'Rome\'s huge Sunday flea market — vintage, kitchenware, leather. Watch your pockets.', 0, 40, true),
                            $this->opt('The Pope\'s Angelus', 'budget', 'St Peter\'s Square at noon', 'A short prayer and blessing from the window — a big moment for many Filipino travellers.', 0, 0),
                            $this->opt('Mass in a Roman basilica', 'budget', 'Santa Maria Maggiore or Santa Maria in Trastevere', 'Free, beautiful, and a quiet last morning.', 0, 0),
                        ],
                    ],
                    [
                        'time' => '13:00', 'title' => 'The long Sunday lunch', 'cost_label' => '~€20–40 food',
                        'description' => 'Romans eat late and slowly on Sundays. Book a trattoria — they fill up with families.',
                    ],
                    [
                        'time' => '15:30', 'title' => 'Last pasalubong', 'cost_label' => '~€20–100',
                        'description' => 'Rosaries from the shops near St Peter\'s, dried pasta, limoncello (check the liquid rules), and chocolate.',
                    ],
                    [
                        'time' => '17:30', 'title' => 'Collect bags → FCO', 'cost_label' => '€14–15',
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
            ['category' => 'Transpo', 'label' => 'Round-trip airfare', 'note' => 'MNL ⇄ FCO, one stop', 'amount' => 1000],
            ['category' => 'Transpo', 'label' => 'PH travel tax + terminal fee', 'amount' => 30],
            ['category' => 'Transpo', 'label' => 'Airport transfers + buses', 'amount' => 40],
            ['category' => 'Accommodation', 'label' => 'Hotel near Piazza Navona', 'note' => 'quad ÷ 4 × 3 nights, plus city tax', 'amount' => 300],
            ['category' => 'Meals', 'label' => 'Per diem', 'note' => '~€18 / meal × 3 × 4 days', 'amount' => 216, 'per_person' => true],
            ['category' => 'Rail & entry', 'label' => 'Colosseum, Vatican, Pantheon, Trevi, Castel Sant\'Angelo', 'amount' => 70],
            ['category' => 'Shopping', 'label' => 'Pasalubong', 'note' => 'rosaries, pasta, chocolate', 'amount' => 60],
            ['category' => 'Insurance', 'label' => 'Travel insurance', 'note' => 'required for the Schengen visa', 'amount' => 40],
            ['category' => 'Communication', 'label' => 'eSIM', 'note' => '~4 days, EU-wide', 'amount' => 10],
            ['category' => 'Visa', 'label' => 'Schengen visa', 'note' => 'plus the visa centre\'s service fee', 'amount' => 90],
        ];
    }
}
