<?php

// Curated airports for the trip-wizard's From/To autocomplete — biased toward
// routes Philippine travelers actually fly. Free text is still accepted for
// anything not listed; CreateTrip resolves against this list by IATA code
// (or name) when it can, purely so analytics has consistent values to group.
return [
    ['code' => 'MNL', 'name' => 'Ninoy Aquino International Airport', 'city' => 'Manila'],
    ['code' => 'CEB', 'name' => 'Mactan-Cebu International Airport', 'city' => 'Cebu'],
    ['code' => 'CRK', 'name' => 'Clark International Airport', 'city' => 'Angeles'],
    ['code' => 'DVO', 'name' => 'Francisco Bangoy International Airport', 'city' => 'Davao'],
    ['code' => 'ILO', 'name' => 'Iloilo International Airport', 'city' => 'Iloilo'],
    ['code' => 'KLO', 'name' => 'Kalibo International Airport', 'city' => 'Kalibo'],
    ['code' => 'PPS', 'name' => 'Puerto Princesa International Airport', 'city' => 'Puerto Princesa'],

    ['code' => 'NRT', 'name' => 'Narita International Airport', 'city' => 'Tokyo'],
    ['code' => 'HND', 'name' => 'Haneda Airport', 'city' => 'Tokyo'],
    ['code' => 'KIX', 'name' => 'Kansai International Airport', 'city' => 'Osaka'],
    ['code' => 'NGO', 'name' => 'Chubu Centrair International Airport', 'city' => 'Nagoya'],
    ['code' => 'FUK', 'name' => 'Fukuoka Airport', 'city' => 'Fukuoka'],
    ['code' => 'CTS', 'name' => 'New Chitose Airport', 'city' => 'Sapporo'],
    ['code' => 'OKA', 'name' => 'Naha Airport', 'city' => 'Okinawa'],
    ['code' => 'ICN', 'name' => 'Incheon International Airport', 'city' => 'Seoul'],
    ['code' => 'GMP', 'name' => 'Gimpo International Airport', 'city' => 'Seoul'],
    ['code' => 'PUS', 'name' => 'Gimhae International Airport', 'city' => 'Busan'],
    ['code' => 'TPE', 'name' => 'Taiwan Taoyuan International Airport', 'city' => 'Taipei'],
    ['code' => 'HKG', 'name' => 'Hong Kong International Airport', 'city' => 'Hong Kong'],
    ['code' => 'MFM', 'name' => 'Macau International Airport', 'city' => 'Macau'],
    ['code' => 'PEK', 'name' => 'Beijing Capital International Airport', 'city' => 'Beijing'],
    ['code' => 'PVG', 'name' => 'Shanghai Pudong International Airport', 'city' => 'Shanghai'],
    ['code' => 'CAN', 'name' => 'Guangzhou Baiyun International Airport', 'city' => 'Guangzhou'],

    ['code' => 'SIN', 'name' => 'Singapore Changi Airport', 'city' => 'Singapore'],
    ['code' => 'BKK', 'name' => 'Suvarnabhumi Airport', 'city' => 'Bangkok'],
    ['code' => 'DMK', 'name' => 'Don Mueang International Airport', 'city' => 'Bangkok'],
    ['code' => 'KUL', 'name' => 'Kuala Lumpur International Airport', 'city' => 'Kuala Lumpur'],
    ['code' => 'CGK', 'name' => 'Soekarno-Hatta International Airport', 'city' => 'Jakarta'],
    ['code' => 'DPS', 'name' => 'Ngurah Rai International Airport', 'city' => 'Denpasar (Bali)'],
    ['code' => 'HAN', 'name' => 'Noi Bai International Airport', 'city' => 'Hanoi'],
    ['code' => 'SGN', 'name' => 'Tan Son Nhat International Airport', 'city' => 'Ho Chi Minh City'],

    ['code' => 'DXB', 'name' => 'Dubai International Airport', 'city' => 'Dubai'],
    ['code' => 'AUH', 'name' => 'Abu Dhabi International Airport', 'city' => 'Abu Dhabi'],
    ['code' => 'DOH', 'name' => 'Hamad International Airport', 'city' => 'Doha'],
    ['code' => 'IST', 'name' => 'Istanbul Airport', 'city' => 'Istanbul'],

    ['code' => 'LHR', 'name' => 'Heathrow Airport', 'city' => 'London'],
    ['code' => 'CDG', 'name' => 'Charles de Gaulle Airport', 'city' => 'Paris'],
    ['code' => 'FRA', 'name' => 'Frankfurt Airport', 'city' => 'Frankfurt'],
    ['code' => 'AMS', 'name' => 'Amsterdam Airport Schiphol', 'city' => 'Amsterdam'],
    ['code' => 'FCO', 'name' => 'Leonardo da Vinci Airport', 'city' => 'Rome'],
    ['code' => 'MAD', 'name' => 'Adolfo Suárez Madrid-Barajas Airport', 'city' => 'Madrid'],
    ['code' => 'BCN', 'name' => 'Barcelona-El Prat Airport', 'city' => 'Barcelona'],

    ['code' => 'LAX', 'name' => 'Los Angeles International Airport', 'city' => 'Los Angeles'],
    ['code' => 'SFO', 'name' => 'San Francisco International Airport', 'city' => 'San Francisco'],
    ['code' => 'JFK', 'name' => 'John F. Kennedy International Airport', 'city' => 'New York'],
    ['code' => 'EWR', 'name' => 'Newark Liberty International Airport', 'city' => 'Newark'],
    ['code' => 'ORD', 'name' => 'O\'Hare International Airport', 'city' => 'Chicago'],
    ['code' => 'SEA', 'name' => 'Seattle-Tacoma International Airport', 'city' => 'Seattle'],
    ['code' => 'YVR', 'name' => 'Vancouver International Airport', 'city' => 'Vancouver'],
    ['code' => 'YYZ', 'name' => 'Toronto Pearson International Airport', 'city' => 'Toronto'],

    ['code' => 'SYD', 'name' => 'Sydney Airport', 'city' => 'Sydney'],
    ['code' => 'MEL', 'name' => 'Melbourne Airport', 'city' => 'Melbourne'],
    ['code' => 'AKL', 'name' => 'Auckland Airport', 'city' => 'Auckland'],
    ['code' => 'GUM', 'name' => 'Guam International Airport', 'city' => 'Guam'],
];
