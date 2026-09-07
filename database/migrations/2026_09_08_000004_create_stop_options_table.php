<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stop_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stop_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('sort')->default(0);

            $table->string('name');
            $table->string('tier', 32)->nullable();  // budget | mid | splurge | indoor-ac | rain-friendly ...
            $table->text('note')->nullable();

            // cost_range -> feeds the live budget worksheet
            $table->unsignedInteger('cost_min')->nullable();
            $table->unsignedInteger('cost_max')->nullable();
            $table->string('currency', 8)->nullable(); // inherits trip currency when null

            $table->string('weather_tag', 12)->nullable(); // indoor | covered | outdoor
            $table->string('map_provider', 16)->nullable();
            $table->text('map_url')->nullable();

            $table->boolean('is_default_pick')->default(false);
            $table->boolean('is_sponsored')->default(false); // Phase: sponsored picks
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stop_options');
    }
};
