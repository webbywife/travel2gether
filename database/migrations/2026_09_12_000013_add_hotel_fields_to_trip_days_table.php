<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trip_days', function (Blueprint $table) {
            // Multi-city trips: an area can have its own hotel, so each day
            // anchors to whichever hotel actually covers it. Null when the
            // day just uses the trip's single main hotel (the common case).
            $table->string('hotel_name', 160)->nullable()->after('area_label');
            $table->string('hotel_address', 255)->nullable()->after('hotel_name');
            $table->decimal('hotel_lat', 10, 7)->nullable()->after('hotel_address');
            $table->decimal('hotel_lon', 10, 7)->nullable()->after('hotel_lat');
        });
    }

    public function down(): void
    {
        Schema::table('trip_days', function (Blueprint $table) {
            $table->dropColumn(['hotel_name', 'hotel_address', 'hotel_lat', 'hotel_lon']);
        });
    }
};
