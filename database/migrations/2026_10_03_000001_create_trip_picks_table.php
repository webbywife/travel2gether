<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 2b — the group's shared pick per stop, replacing per-device
 * localStorage. One row per (trip, stop); picked_by gives attribution.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trip_picks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stop_option_id')->constrained()->cascadeOnDelete();
            $table->foreignId('picked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['trip_id', 'stop_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trip_picks');
    }
};
