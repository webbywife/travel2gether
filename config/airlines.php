<?php

// Curated airlines for the trip-wizard's Airline autocomplete — the carriers
// Philippine travelers actually fly. Free text still works for anything
// else; CreateTrip resolves against this list (by IATA code or name) when
// it can, so analytics has consistent values to group on.
return [
    ['code' => 'PR', 'name' => 'Philippine Airlines'],
    ['code' => '5J', 'name' => 'Cebu Pacific'],
    ['code' => 'Z2', 'name' => 'AirAsia Philippines'],
    ['code' => 'DG', 'name' => 'Cebgo'],

    ['code' => 'CX', 'name' => 'Cathay Pacific'],
    ['code' => 'KA', 'name' => 'Cathay Dragon'],
    ['code' => 'SQ', 'name' => 'Singapore Airlines'],
    ['code' => 'TR', 'name' => 'Scoot'],
    ['code' => 'AK', 'name' => 'AirAsia'],
    ['code' => 'D7', 'name' => 'AirAsia X'],
    ['code' => 'TG', 'name' => 'Thai Airways'],
    ['code' => 'MH', 'name' => 'Malaysia Airlines'],
    ['code' => 'GA', 'name' => 'Garuda Indonesia'],
    ['code' => 'VN', 'name' => 'Vietnam Airlines'],
    ['code' => 'PG', 'name' => 'Bangkok Airways'],

    ['code' => 'NH', 'name' => 'All Nippon Airways (ANA)'],
    ['code' => 'JL', 'name' => 'Japan Airlines'],
    ['code' => 'MM', 'name' => 'Peach Aviation'],
    ['code' => 'KE', 'name' => 'Korean Air'],
    ['code' => 'OZ', 'name' => 'Asiana Airlines'],
    ['code' => 'BX', 'name' => 'Air Busan'],
    ['code' => 'CI', 'name' => 'China Airlines'],
    ['code' => 'BR', 'name' => 'EVA Air'],
    ['code' => 'CA', 'name' => 'Air China'],
    ['code' => 'MU', 'name' => 'China Eastern'],
    ['code' => 'CZ', 'name' => 'China Southern'],

    ['code' => 'EK', 'name' => 'Emirates'],
    ['code' => 'EY', 'name' => 'Etihad Airways'],
    ['code' => 'QR', 'name' => 'Qatar Airways'],
    ['code' => 'TK', 'name' => 'Turkish Airlines'],

    ['code' => 'BA', 'name' => 'British Airways'],
    ['code' => 'AF', 'name' => 'Air France'],
    ['code' => 'LH', 'name' => 'Lufthansa'],
    ['code' => 'KL', 'name' => 'KLM'],
    ['code' => 'AZ', 'name' => 'ITA Airways'],
    ['code' => 'IB', 'name' => 'Iberia'],

    ['code' => 'UA', 'name' => 'United Airlines'],
    ['code' => 'DL', 'name' => 'Delta Air Lines'],
    ['code' => 'AA', 'name' => 'American Airlines'],
    ['code' => 'AC', 'name' => 'Air Canada'],
    ['code' => 'HA', 'name' => 'Hawaiian Airlines'],

    ['code' => 'QF', 'name' => 'Qantas'],
    ['code' => 'VA', 'name' => 'Virgin Australia'],
    ['code' => 'NZ', 'name' => 'Air New Zealand'],
    ['code' => 'UO', 'name' => 'Hong Kong Express'],
];
