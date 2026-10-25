<?php

// Booking links on the trip page. Each slot is a full URL template from your affiliate program, set in .env; a slot with
// no URL is not shown, and the whole "Book your trip" card is hidden when none are set. Placeholders (URL-encoded for you):
//   {destination}  {city}  {checkin}  {checkout}  {adults}
// Example: AFFILIATE_STAYS_URL="https://www.example.com/search?q={destination}&from={checkin}&to={checkout}&ref=YOURID"
return [
    'stays' => ['label' => 'Find a place to stay', 'url' => env('AFFILIATE_STAYS_URL')],
    'things' => ['label' => 'Tours & tickets', 'url' => env('AFFILIATE_THINGS_URL')],
    'esim' => ['label' => 'Travel eSIM', 'url' => env('AFFILIATE_ESIM_URL')],
    'insurance' => ['label' => 'Travel insurance', 'url' => env('AFFILIATE_INSURANCE_URL')],
];
