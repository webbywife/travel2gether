<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('place_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('place_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->smallInteger('vote'); // +1 thumbs up, -1 thumbs down
            $table->timestamps();

            $table->unique(['place_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('place_recommendations');
    }
};
