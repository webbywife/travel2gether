<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trip_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('day_number');
            $table->date('date');
            $table->string('title');
            $table->string('title_secondary')->nullable(); // e.g. Korean weekday
            $table->text('summary')->nullable();

            // Weather / outfit block
            $table->string('weather_tag', 12)->default('outdoor'); // indoor | covered | outdoor
            $table->date('forecast_date')->nullable();
            $table->smallInteger('temp_high')->nullable();
            $table->smallInteger('temp_low')->nullable();
            $table->text('weather_note')->nullable();
            $table->json('outfit_chips')->nullable();   // ["breathable tee", ...]
            $table->json('outfit_photos')->nullable();  // [{url, label, alt}]

            // "Today's area" map
            $table->string('area_label')->nullable();
            $table->text('map_embed_url')->nullable();

            // Required by the schema contract: every day documents its failure modes
            $table->json('hiccups'); // ["stall closes early", ...] -- NOT NULL on purpose

            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();

            $table->unique(['trip_id', 'day_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trip_days');
    }
};
