<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            // Anchor point for the live forecast (Phase 3 weather engine).
            $table->decimal('lat', 9, 6)->nullable()->after('map_provider');
            $table->decimal('lon', 9, 6)->nullable()->after('lat');
        });
    }

    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropColumn(['lat', 'lon']);
        });
    }
};
