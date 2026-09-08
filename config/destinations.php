<?php

/**
 * Curated reference data: the world's most-visited countries (UNWTO-style
 * ranking) plus the PH-neighbourhood destinations Travel2gether's own trips
 * lean on, each with a handful of standout cities and a visa note for a
 * Philippine passport holder — the traveller profile the whole app assumes
 * (every seeded sample and the wizard default to an MNL origin).
 *
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

    ['country' => 'Thailand', 'region' => 'Southeast Asia', 'flag' => '🇹🇭',
        'cities' => ['Bangkok', 'Chiang Mai', 'Phuket', 'Krabi', 'Ayutthaya'],
        'visa_status' => 'visa_free', 'visa_note' => 'Visa-free for up to 30 days (ASEAN).'],

    ['country' => 'Malaysia', 'region' => 'Southeast Asia', 'flag' => '🇲🇾',
        'cities' => ['Kuala Lumpur', 'Penang', 'Langkawi', 'Malacca', 'Kota Kinabalu'],
        'visa_status' => 'visa_free', 'visa_note' => 'Visa-free for up to 30 days (ASEAN).'],

    ['country' => 'Singapore', 'region' => 'Southeast Asia', 'flag' => '🇸🇬',
        'cities' => ['Marina Bay', 'Sentosa', 'Chinatown', 'Orchard Road', 'Kampong Glam'],
        'visa_status' => 'visa_free', 'visa_note' => 'Visa-free for up to 30 days (ASEAN).'],

    ['country' => 'Indonesia', 'region' => 'Southeast Asia', 'flag' => '🇮🇩',
        'cities' => ['Bali (Ubud & Seminyak)', 'Jakarta', 'Yogyakarta', 'Bandung', 'Lombok'],
        'visa_status' => 'visa_free', 'visa_note' => 'Visa-free for up to 30 days (ASEAN).'],

    ['country' => 'Vietnam', 'region' => 'Southeast Asia', 'flag' => '🇻🇳',
        'cities' => ['Hanoi', 'Ho Chi Minh City', 'Da Nang', 'Hoi An', 'Ha Long Bay'],
        'visa_status' => 'visa_free', 'visa_note' => 'Visa-free for up to 21 days (ASEAN).'],

    ['country' => 'Hong Kong SAR', 'region' => 'East Asia', 'flag' => '🇭🇰',
        'cities' => ['Hong Kong Island', 'Kowloon', 'Lantau Island', 'New Territories'],
        'visa_status' => 'visa_free', 'visa_note' => 'Visa-free for up to 14 days.'],

    ['country' => 'Taiwan', 'region' => 'East Asia', 'flag' => '🇹🇼',
        'cities' => ['Taipei', 'Kaohsiung', 'Tainan', 'Hualien', 'Sun Moon Lake'],
        'visa_status' => 'evisa', 'visa_note' => 'Visa-free entry via an online Travel Authorization Certificate, or with a qualifying US/Canada/Japan/Schengen/UK/Australia/NZ visa.'],

    ['country' => 'Japan', 'region' => 'East Asia', 'flag' => '🇯🇵',
        'cities' => ['Tokyo', 'Kyoto', 'Osaka', 'Sapporo', 'Fukuoka'],
        'visa_status' => 'required', 'visa_note' => 'Tourist visa needed, usually filed through an accredited travel agency; a limited eVisa scheme has been expanding.'],

    ['country' => 'South Korea', 'region' => 'East Asia', 'flag' => '🇰🇷',
        'cities' => ['Seoul', 'Busan', 'Jeju Island', 'Gyeongju', 'Incheon'],
        'visa_status' => 'required', 'visa_note' => 'Tourist visa required.'],

    ['country' => 'China', 'region' => 'East Asia', 'flag' => '🇨🇳',
        'cities' => ['Beijing', 'Shanghai', "Xi'an", 'Chengdu', 'Guilin'],
        'visa_status' => 'required', 'visa_note' => 'Visa required; an e-visa option is available for some entry points.'],

    ['country' => 'United Arab Emirates', 'region' => 'Middle East', 'flag' => '🇦🇪',
        'cities' => ['Dubai', 'Abu Dhabi', 'Sharjah', 'Ras Al Khaimah'],
        'visa_status' => 'required', 'visa_note' => 'Visa required, though visa-on-arrival is often granted when holding a valid US, UK, Schengen, Australian or Canadian visa.'],

    ['country' => 'France', 'region' => 'Europe', 'flag' => '🇫🇷',
        'cities' => ['Paris', 'Nice', 'Lyon', 'Bordeaux', 'Marseille'],
        'visa_status' => 'required', 'visa_note' => 'Schengen visa required.'],

    ['country' => 'Spain', 'region' => 'Europe', 'flag' => '🇪🇸',
        'cities' => ['Barcelona', 'Madrid', 'Seville', 'Valencia', 'Granada'],
        'visa_status' => 'required', 'visa_note' => 'Schengen visa required.'],

    ['country' => 'Italy', 'region' => 'Europe', 'flag' => '🇮🇹',
        'cities' => ['Rome', 'Florence', 'Venice', 'Milan', 'Naples'],
        'visa_status' => 'required', 'visa_note' => 'Schengen visa required.'],

    ['country' => 'Germany', 'region' => 'Europe', 'flag' => '🇩🇪',
        'cities' => ['Berlin', 'Munich', 'Hamburg', 'Cologne', 'Frankfurt'],
        'visa_status' => 'required', 'visa_note' => 'Schengen visa required.'],

    ['country' => 'Greece', 'region' => 'Europe', 'flag' => '🇬🇷',
        'cities' => ['Athens', 'Santorini', 'Mykonos', 'Crete', 'Rhodes'],
        'visa_status' => 'required', 'visa_note' => 'Schengen visa required.'],

    ['country' => 'Austria', 'region' => 'Europe', 'flag' => '🇦🇹',
        'cities' => ['Vienna', 'Salzburg', 'Innsbruck', 'Hallstatt'],
        'visa_status' => 'required', 'visa_note' => 'Schengen visa required.'],

    ['country' => 'Netherlands', 'region' => 'Europe', 'flag' => '🇳🇱',
        'cities' => ['Amsterdam', 'Rotterdam', 'The Hague', 'Utrecht', 'Giethoorn'],
        'visa_status' => 'required', 'visa_note' => 'Schengen visa required.'],

    ['country' => 'Portugal', 'region' => 'Europe', 'flag' => '🇵🇹',
        'cities' => ['Lisbon', 'Porto', 'Sintra', 'the Algarve', 'Madeira'],
        'visa_status' => 'required', 'visa_note' => 'Schengen visa required.'],

    ['country' => 'Turkey', 'region' => 'Europe/Asia', 'flag' => '🇹🇷',
        'cities' => ['Istanbul', 'Cappadocia', 'Antalya', 'Izmir', 'Pamukkale'],
        'visa_status' => 'evisa', 'visa_note' => 'e-Visa available to apply for online before you fly.'],

    ['country' => 'United Kingdom', 'region' => 'Europe', 'flag' => '🇬🇧',
        'cities' => ['London', 'Edinburgh', 'Manchester', 'Bath', 'Liverpool'],
        'visa_status' => 'required', 'visa_note' => 'UK Standard Visitor visa required.'],

    ['country' => 'United States', 'region' => 'Americas', 'flag' => '🇺🇸',
        'cities' => ['New York City', 'Los Angeles', 'San Francisco', 'Las Vegas', 'Honolulu'],
        'visa_status' => 'required', 'visa_note' => 'B1/B2 visitor visa required, including an interview.'],

    ['country' => 'Canada', 'region' => 'Americas', 'flag' => '🇨🇦',
        'cities' => ['Toronto', 'Vancouver', 'Montreal', 'Banff', 'Quebec City'],
        'visa_status' => 'required', 'visa_note' => 'Visitor visa required, unless you hold a valid US visa (then an eTA may suffice).'],

    ['country' => 'Mexico', 'region' => 'Americas', 'flag' => '🇲🇽',
        'cities' => ['Mexico City', 'Cancún', 'Playa del Carmen', 'Oaxaca', 'Tulum'],
        'visa_status' => 'required', 'visa_note' => 'Visa required, unless you hold a valid US, Schengen, UK or Japanese visa.'],

    ['country' => 'Australia', 'region' => 'Oceania', 'flag' => '🇦🇺',
        'cities' => ['Sydney', 'Melbourne', 'Brisbane', 'Gold Coast', 'Perth'],
        'visa_status' => 'evisa', 'visa_note' => 'Visitor visa (subclass 600) applied for online.'],

];
