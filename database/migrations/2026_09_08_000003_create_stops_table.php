<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_day_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('sort')->default(0);

            $table->string('time', 32)->nullable();   // "09:00" or "Morning"
            $table->string('title');
            $table->text('description')->nullable();

            $table->string('cost_label', 48)->nullable(); // quick inline tag e.g. "Free", "~PHP 900"
            $table->string('weather_tag', 12)->nullable(); // indoor | covered | outdoor
            $table->string('map_provider', 16)->nullable(); // overrides trip default
            $table->text('map_url')->nullable();
            $table->text('thumb_url')->nullable();
            $table->text('hiccup')->nullable(); // optional stop-specific failure mode

            // Option slot: 3+ alternatives the group taps through
            $table->boolean('has_options')->default(false);
            $table->string('option_label')->nullable(); // "Lunch options", "Where to shop"

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stops');
    }
};
