<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trip_segments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('sort')->default(0); // 0 = outbound, 1 = return, ...

            // Free text as typed — always kept, even when a code/name below
            // was also resolved, so the flight pass always has something to show.
            $table->string('from_text', 60)->nullable();
            $table->string('to_text', 60)->nullable();
            $table->string('airline_text', 60)->nullable();

            // Resolved against config/airports.php + config/airlines.php when
            // the typed text matches a known IATA code or name — null when it
            // doesn't. This is what analytics groups on.
            $table->string('from_code', 4)->nullable()->index();
            $table->string('to_code', 4)->nullable()->index();
            $table->string('airline_code', 4)->nullable()->index();
            $table->string('airline_name', 60)->nullable();

            $table->date('date')->nullable()->index(); // the real calendar date, not a display string
            $table->string('flight_no', 20)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trip_segments');
    }
};
