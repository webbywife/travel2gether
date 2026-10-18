<?php

/**
 * Curated reference data: the world's most-visited countries (UNWTO-style
 * ranking) plus the PH-neighbourhood destinations Travel2gether's own trips
 * lean on, each with a handful of standout cities and a visa note for a
 * Philippine passport holder — the traveller profile the whole app assumes
 * (every seeded sample and the wizard default to an MNL origin).
 *
 * Visa notes last fact-checked 2026-10-18 (Thailand back to 30 days from 15 Sep 2026;
 * Taiwan trial to 31 Jul 2027; UAE visa-on-arrival from Jun 2026; Japan eVisa from May 2026).
 * Visa rules change and have exceptions (transit visas, accredited-agency
 * schemes, holding a qualifying third-country visa, etc.) — `visa_note` is a
 * starting point, not a ruling. Always confirm with the destination's
 * embassy or official immigration site before booking.
 *
 * visa_status: one of
 *   'visa_free' — no visa needed for a short tourist stay
 *   'evisa'     — an online e-Visa / travel authorization covers it
 *   'required'  — a visa (often with an interview) is needed
 */

return [

    ['country' => 'Thailand', 'currency' => 'THB', 'region' => 'Southeast Asia', 'flag' => '🇹🇭',
        'cities' => ['Bangkok', 'Chiang Mai', 'Phuket', 'Krabi', 'Ayutthaya'],
        'visa_status' => 'visa_free', 'visa_note' => 'Visa-free for up to 30 days (ASEAN).'],

    ['country' => 'Malaysia', 'currency' => 'MYR', 'region' => 'Southeast Asia', 'flag' => '🇲🇾',
        'cities' => ['Kuala Lumpur', 'Penang', 'Langkawi', 'Malacca', 'Kota Kinabalu'],
        'visa_status' => 'visa_free', 'visa_note' => 'Visa-free for up to 30 days (ASEAN).'],

    ['country' => 'Singapore', 'currency' => 'SGD', 'region' => 'Southeast Asia', 'flag' => '🇸🇬',
        'cities' => ['Marina Bay', 'Sentosa', 'Chinatown', 'Orchard Road', 'Kampong Glam'],
        'visa_status' => 'visa_free', 'visa_note' => 'Visa-free for up to 30 days (ASEAN).'],

    ['country' => 'Indonesia', 'currency' => 'IDR', 'region' => 'Southeast Asia', 'flag' => '🇮🇩',
        'cities' => ['Bali (Ubud & Seminyak)', 'Jakarta', 'Yogyakarta', 'Bandung', 'Lombok'],
        'visa_status' => 'visa_free', 'visa_note' => 'Visa-free for up to 30 days (ASEAN).'],

    ['country' => 'Vietnam', 'currency' => 'VND', 'region' => 'Southeast Asia', 'flag' => '🇻🇳',
        'cities' => ['Hanoi', 'Ho Chi Minh City', 'Da Nang', 'Hoi An', 'Ha Long Bay'],
        'visa_status' => 'visa_free', 'visa_note' => 'Visa-free for up to 21 days (ASEAN).'],

    ['country' => 'Hong Kong SAR', 'currency' => 'HKD', 'region' => 'East Asia', 'flag' => '🇭🇰',
        'cities' => ['Hong Kong Island', 'Kowloon', 'Lantau Island', 'New Territories'],
        // Matched before "China", so "Hong Kong, China" isn't read as mainland China (visa required).
        'aliases' => ['Hong Kong', 'HKSAR'],
        'visa_status' => 'visa_free', 'visa_note' => 'Visa-free for up to 14 days.'],

    ['country' => 'Macao SAR', 'currency' => 'MOP', 'region' => 'East Asia', 'flag' => '🇲🇴',
        'cities' => ['Macao Peninsula', 'Taipa', 'Cotai', 'Coloane'],
        'aliases' => ['Macao', 'Macau'],
        'visa_status' => 'visa_free', 'visa_note' => 'Visa-free for up to 30 days.'],

    ['country' => 'Taiwan', 'currency' => 'TWD', 'region' => 'East Asia', 'flag' => '🇹🇼',
        'cities' => ['Taipei', 'Kaohsiung', 'Tainan', 'Hualien', 'Sun Moon Lake'],
        'visa_status' => 'visa_free', 'visa_note' => 'Visa-free for up to 14 days (trial programme extended to 31 Jul 2027 — re-check before booking).'],

    ['country' => 'Japan', 'currency' => 'JPY', 'region' => 'East Asia', 'flag' => '🇯🇵',
        'cities' => ['Tokyo', 'Kyoto', 'Osaka', 'Sapporo', 'Fukuoka'],
        'visa_status' => 'required', 'visa_note' => 'Tourist visa required, applied for through the Japan Visa Application Centre. Since May 2026 an eVisa also covers package tours run by designated agencies.'],

    ['country' => 'South Korea', 'currency' => 'KRW', 'region' => 'East Asia', 'flag' => '🇰🇷',
        'cities' => ['Seoul', 'Busan', 'Jeju Island', 'Gyeongju', 'Incheon'],
        'visa_status' => 'required', 'visa_note' => 'Tourist visa required.'],

    ['country' => 'China', 'currency' => 'CNY', 'region' => 'East Asia', 'flag' => '🇨🇳',
        'cities' => ['Beijing', 'Shanghai', "Xi'an", 'Chengdu', 'Guilin'],
        'visa_status' => 'required', 'visa_note' => 'Tourist visa required. Visa-free transit (up to 240 hours) may apply when connecting through China to a third country.'],

    ['country' => 'United Arab Emirates', 'currency' => 'AED', 'region' => 'Middle East', 'flag' => '🇦🇪',
        'cities' => ['Dubai', 'Abu Dhabi', 'Sharjah', 'Ras Al Khaimah'],
        'visa_status' => 'required', 'visa_note' => 'Visa required, unless you hold a valid US, UK or EU visa or residence permit (or a US Green Card) — then a visa on arrival is available (since June 2026).'],

    ['country' => 'France', 'currency' => 'EUR', 'region' => 'Europe', 'flag' => '🇫🇷',
        'cities' => ['Paris', 'Nice', 'Lyon', 'Bordeaux', 'Marseille'],
        'visa_status' => 'required', 'visa_note' => 'Schengen visa required.'],

    ['country' => 'Spain', 'currency' => 'EUR', 'region' => 'Europe', 'flag' => '🇪🇸',
        'cities' => ['Barcelona', 'Madrid', 'Seville', 'Valencia', 'Granada'],
        'visa_status' => 'required', 'visa_note' => 'Schengen visa required.'],

    ['country' => 'Italy', 'currency' => 'EUR', 'region' => 'Europe', 'flag' => '🇮🇹',
        'cities' => ['Rome', 'Florence', 'Venice', 'Milan', 'Naples'],
        'visa_status' => 'required', 'visa_note' => 'Schengen visa required.'],

    ['country' => 'Germany', 'currency' => 'EUR', 'region' => 'Europe', 'flag' => '🇩🇪',
        'cities' => ['Berlin', 'Munich', 'Hamburg', 'Cologne', 'Frankfurt'],
        'visa_status' => 'required', 'visa_note' => 'Schengen visa required.'],

    ['country' => 'Greece', 'currency' => 'EUR', 'region' => 'Europe', 'flag' => '🇬🇷',
        'cities' => ['Athens', 'Santorini', 'Mykonos', 'Crete', 'Rhodes'],
        'visa_status' => 'required', 'visa_note' => 'Schengen visa required.'],

    ['country' => 'Austria', 'currency' => 'EUR', 'region' => 'Europe', 'flag' => '🇦🇹',
        'cities' => ['Vienna', 'Salzburg', 'Innsbruck', 'Hallstatt'],
        'visa_status' => 'required', 'visa_note' => 'Schengen visa required.'],

    ['country' => 'Netherlands', 'currency' => 'EUR', 'region' => 'Europe', 'flag' => '🇳🇱',
        'cities' => ['Amsterdam', 'Rotterdam', 'The Hague', 'Utrecht', 'Giethoorn'],
        'visa_status' => 'required', 'visa_note' => 'Schengen visa required.'],

    ['country' => 'Portugal', 'currency' => 'EUR', 'region' => 'Europe', 'flag' => '🇵🇹',
        'cities' => ['Lisbon', 'Porto', 'Sintra', 'the Algarve', 'Madeira'],
        'visa_status' => 'required', 'visa_note' => 'Schengen visa required.'],

    ['country' => 'Turkey', 'currency' => 'TRY', 'region' => 'Europe/Asia', 'flag' => '🇹🇷',
        'cities' => ['Istanbul', 'Cappadocia', 'Antalya', 'Izmir', 'Pamukkale'],
        'visa_status' => 'required', 'visa_note' => 'Visa required. An online e-Visa is available only if you hold a valid US, UK, Schengen or Irish visa or residence permit; otherwise apply at the embassy.'],

    ['country' => 'United Kingdom', 'currency' => 'GBP', 'region' => 'Europe', 'flag' => '🇬🇧',
        'cities' => ['London', 'Edinburgh', 'Manchester', 'Bath', 'Liverpool'],
        'visa_status' => 'required', 'visa_note' => 'UK Standard Visitor visa required.'],

    ['country' => 'United States', 'currency' => 'USD', 'region' => 'Americas', 'flag' => '🇺🇸',
        'cities' => ['New York City', 'Los Angeles', 'San Francisco', 'Las Vegas', 'Honolulu'],
        'visa_status' => 'required', 'visa_note' => 'B1/B2 visitor visa required, including an interview.'],

    ['country' => 'Canada', 'currency' => 'CAD', 'region' => 'Americas', 'flag' => '🇨🇦',
        'cities' => ['Toronto', 'Vancouver', 'Montreal', 'Banff', 'Quebec City'],
        'visa_status' => 'required', 'visa_note' => 'Visitor visa required. Flying in with a valid US non-immigrant visa (or a Canadian visa from the last 10 years)? An eTA may be enough instead.'],

    ['country' => 'Mexico', 'currency' => 'MXN', 'region' => 'Americas', 'flag' => '🇲🇽',
        'cities' => ['Mexico City', 'Cancún', 'Playa del Carmen', 'Oaxaca', 'Tulum'],
        'visa_status' => 'required', 'visa_note' => 'Visa required, unless you hold a valid US, Canadian, UK, Japanese or Schengen visa or residence permit.'],

    ['country' => 'Australia', 'currency' => 'AUD', 'region' => 'Oceania', 'flag' => '🇦🇺',
        'cities' => ['Sydney', 'Melbourne', 'Brisbane', 'Gold Coast', 'Perth'],
        'visa_status' => 'evisa', 'visa_note' => 'Visitor visa (subclass 600) applied for online.'],

];
