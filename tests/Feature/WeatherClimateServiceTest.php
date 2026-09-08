<?php

namespace Tests\Feature;

use App\Services\WeatherClimateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WeatherClimateServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_averages_temperatures_and_computes_a_rain_chance_across_years(): void
    {
        Http::fake([
            'archive-api.open-meteo.com/*' => Http::sequence()
                ->push(['daily' => [
                    'time' => ['2026-04-01', '2026-04-02'],
                    'temperature_2m_max' => [20, 22],
                    'temperature_2m_min' => [10, 12],
                    'precipitation_sum' => [0, 5],
                ]])
                ->push(['daily' => [
                    'time' => ['2025-04-01', '2025-04-02'],
                    'temperature_2m_max' => [18, 16],
                    'temperature_2m_min' => [8, 6],
                    'precipitation_sum' => [0, 0],
                ]]),
        ]);

        $normals = app(WeatherClimateService::class)->normalsFor(35.0, 135.7, new \DateTime('2027-04-01'), years: 2);

        $this->assertNotNull($normals);
        $this->assertSame(19, $normals['temp_high']); // avg(20,22,18,16)=19
        $this->assertSame(9, $normals['temp_low']);   // avg(10,12,8,6)=9
        $this->assertSame(25, $normals['rain_chance']); // 1 of 4 sampled days had >1mm
    }

    public function test_it_returns_null_when_the_archive_api_is_unreachable(): void
    {
        Http::fake(['archive-api.open-meteo.com/*' => Http::response(null, 500)]);

        $normals = app(WeatherClimateService::class)->normalsFor(35.0, 135.7, new \DateTime('2027-04-01'), years: 2);

        $this->assertNull($normals);
    }

    public function test_results_are_cached_so_the_api_is_hit_once_per_place_and_date(): void
    {
        Http::fake(['archive-api.open-meteo.com/*' => Http::response(['daily' => [
            'time' => ['2026-04-01'], 'temperature_2m_max' => [20], 'temperature_2m_min' => [10], 'precipitation_sum' => [0],
        ]])]);

        $svc = app(WeatherClimateService::class);
        $svc->normalsFor(35.0, 135.7, new \DateTime('2027-04-01'), years: 1);
        $svc->normalsFor(35.0, 135.7, new \DateTime('2027-04-01'), years: 1);

        Http::assertSentCount(1);
    }
}
