<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('places', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 16)->default('google');   // google | kakao | naver
            $table->string('provider_id');                        // e.g. Google place_id
            $table->string('name');
            $table->string('formatted_address')->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lon', 10, 7)->nullable();
            $table->json('types')->nullable();
            $table->decimal('rating', 2, 1)->nullable();
            $table->unsignedInteger('rating_count')->nullable();
            $table->string('price_level', 24)->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->json('raw')->nullable();
            $table->timestamp('details_fetched_at')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'provider_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('places');
    }
};
