<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->string('hotel_name')->nullable()->after('lon');
            $table->string('hotel_address')->nullable()->after('hotel_name');
            $table->json('interests')->nullable()->after('hotel_address'); // ["food","hiking",...] for AI drafting
        });

        Schema::table('trip_days', function (Blueprint $table) {
            // How this day was filled: 'skeleton' (from the wizard) or 'ai' or 'manual'.
            $table->string('source', 16)->default('manual')->after('sort');
            // The day's actual area — the live forecast + AI weather note use this,
            // not the hotel, so a day trip shows its own conditions.
            $table->decimal('lat', 9, 6)->nullable()->after('map_embed_url');
            $table->decimal('lon', 9, 6)->nullable()->after('lat');
        });
    }

    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropColumn(['hotel_name', 'hotel_address', 'interests']);
        });
        Schema::table('trip_days', function (Blueprint $table) {
            $table->dropColumn(['source', 'lat', 'lon']);
        });
    }
};
