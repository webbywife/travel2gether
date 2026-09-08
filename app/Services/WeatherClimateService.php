<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Historical climate normals from Open-Meteo's archive (free, no key) — what a
 * given place is *usually* like on a given calendar date. Trips are almost
 * always planned further out than any live forecast window, so this is what
 * grounds the AI's weather note in real data instead of an invented guess.
 * The client-side live forecast (Open-Meteo forecast API) still takes over
 * once a day falls inside its ~16-day window.
 */
class WeatherClimateService
{
    private const ARCHIVE_URL = 'https://archive-api.open-meteo.com/v1/archive';

    /**
     * @return array{temp_high:int, temp_low:int, rain_chance:int, years_sampled:int}|null
     */
    public function normalsFor(float $lat, float $lon, \DateTimeInterface $date, int $years = 5): ?array
    {
        $target = Carbon::instance($date);
        $cacheKey = 'climate:' . round($lat, 2) . ',' . round($lon, 2) . ':' . $target->format('m-d');

        return Cache::remember($cacheKey, now()->addDays(30), function () use ($lat, $lon, $target, $years) {
            return $this->fetch($lat, $lon, $target, $years);
        });
    }

    /**
     * @return array{temp_high:int, temp_low:int, rain_chance:int, years_sampled:int}|null
     */
    private function fetch(float $lat, float $lon, Carbon $target, int $years): ?array
    {
        $highs = $lows = [];
        $rainyDays = $totalDays = 0;
        $lastYearWithData = $target->year - 1;

        // A ±2 day window around the anniversary date, sampled over the last
        // $years years, gives a decent-sized sample without many HTTP calls.
        for ($y = $lastYearWithData; $y > $lastYearWithData - $years; $y--) {
            $anniversary = $target->copy()->year($y);
            $start = $anniversary->copy()->subDays(2);
            $end = $anniversary->copy()->addDays(2);

            $response = Http::timeout(8)->get(self::ARCHIVE_URL, [
                'latitude' => $lat,
                'longitude' => $lon,
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
                'daily' => 'temperature_2m_max,temperature_2m_min,precipitation_sum',
                'timezone' => 'auto',
            ]);

            if (! $response->successful()) {
                continue;
            }

            $daily = $response->json('daily');
            if (! is_array($daily) || empty($daily['time'])) {
                continue;
            }

            foreach ($daily['time'] as $i => $date) {
                $max = $daily['temperature_2m_max'][$i] ?? null;
                $min = $daily['temperature_2m_min'][$i] ?? null;
                $rain = $daily['precipitation_sum'][$i] ?? null;

                if ($max === null || $min === null) {
                    continue;
                }

                $highs[] = $max;
                $lows[] = $min;
                $totalDays++;
                if (($rain ?? 0) > 1.0) {
                    $rainyDays++;
                }
            }
        }

        if (empty($highs)) {
            return null;
        }

        return [
            'temp_high' => (int) round(array_sum($highs) / count($highs)),
            'temp_low' => (int) round(array_sum($lows) / count($lows)),
            'rain_chance' => (int) round(($rainyDays / max($totalDays, 1)) * 100),
            'years_sampled' => $years,
        ];
    }
}
