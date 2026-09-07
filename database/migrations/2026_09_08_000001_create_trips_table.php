<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('tagline')->nullable();
            $table->text('subhead')->nullable();
            $table->string('destination');
            $table->string('origin_label')->nullable();   // e.g. "MNL -> ICN -> MNL"
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedSmallInteger('party_size')->nullable();
            $table->string('currency', 8)->default('PHP');
            $table->string('map_provider', 16)->default('google'); // google | naver | kakao
            $table->text('forecast_note')->nullable();

            // Structured extras rendered on the hero
            $table->json('segments')->nullable();  // boarding-pass flight segments
            $table->json('stats')->nullable();     // [{value, label}] stat cards

            $table->boolean('is_public')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
