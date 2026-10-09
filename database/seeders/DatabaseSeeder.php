<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            Seoul2026Seeder::class,
            Tokyo2026Seeder::class,
            Singapore2027Seeder::class,
            Kyoto2026Seeder::class,
            Osaka2027Seeder::class,
        ]);
    }
}
