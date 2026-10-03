<?php

namespace Tests\Unit;

use App\Support\WeatherFit;
use PHPUnit\Framework\TestCase;

class WeatherFitTest extends TestCase
{
    public function test_explicit_tags_are_normalised_and_junk_is_rejected(): void
    {
        $this->assertSame('indoor', WeatherFit::normalize(' Indoor '));
        $this->assertNull(WeatherFit::normalize('sunny'));
        $this->assertNull(WeatherFit::normalize(null));
    }

    public function test_it_guesses_from_name_tier_and_note(): void
    {
        $this->assertSame('indoor', WeatherFit::infer('Kaiyukan Aquarium'));
        $this->assertSame('indoor', WeatherFit::infer('Somewhere', 'indoor-ac'));
        $this->assertSame('indoor', WeatherFit::infer('Garden Café'));          // café beats garden
        $this->assertSame('covered', WeatherFit::infer('Kuromon Market'));
        $this->assertSame('outdoor', WeatherFit::infer('Nara Park'));
        $this->assertSame('outdoor', WeatherFit::infer('Unknown thing'));      // safe default
        $this->assertSame('indoor', WeatherFit::infer('Hyeongje Teuksubuwi', null, null, 'Dinner in Gangnam'));
    }
}
