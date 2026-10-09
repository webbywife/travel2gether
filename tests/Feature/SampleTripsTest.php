<?php

namespace Tests\Feature;

use App\Models\Trip;
use Database\Seeders\Kyoto2026Seeder;
use Database\Seeders\Osaka2027Seeder;
use Database\Seeders\Singapore2027Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SampleTripsTest extends TestCase
{
    use RefreshDatabase;

    public static function samples(): array
    {
        return [
            'singapore' => [Singapore2027Seeder::class, 'singapore-2027', 4, 'Garden Rhapsody'],
            'kyoto' => [Kyoto2026Seeder::class, 'kyoto-2026', 4, 'Kiyomizu-dera'],
            'osaka' => [Osaka2027Seeder::class, 'osaka-2027', 4, 'Tōdai-ji'],
        ];
    }

    #[DataProvider('samples')]
    public function test_each_sample_is_public_complete_and_renders(string $seeder, string $slug, int $days, string $highlight): void
    {
        $this->seed($seeder);
        $this->seed($seeder);   // re-running replaces it instead of duplicating

        $trip = Trip::where('slug', $slug)->sole();
        $this->assertTrue($trip->isSample());
        $this->assertCount($days, $trip->days);

        foreach ($trip->days as $day) {
            $this->assertNotEmpty($day->hiccups, "Day {$day->day_number} needs its hiccups");
            $optionSlots = $day->stops->where('has_options', true);
            $this->assertNotEmpty($optionSlots, "Day {$day->day_number} needs at least one choice for the group");
            foreach ($optionSlots as $stop) {
                $this->assertGreaterThanOrEqual(3, $stop->options->count());
                $this->assertSame(1, $stop->options->where('is_default_pick', true)->count());
            }
        }

        $this->get(route('trips.show', $trip))->assertOk()
            ->assertSee($highlight)
            ->assertSee('Sample plan')
            ->assertSee('<link rel="canonical"', false);
        $this->get('/sitemap.xml')->assertSee(route('trips.show', $trip), false);
    }
}
