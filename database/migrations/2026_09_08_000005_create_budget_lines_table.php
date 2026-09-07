<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('sort')->default(0);

            $table->string('category', 48);  // Flights | Stay | Food | Transport | Activities | Shopping | Buffer
            $table->string('label');
            $table->string('note')->nullable();
            $table->unsignedInteger('amount')->default(0); // default value, editable client-side
            $table->boolean('per_person')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_lines');
    }
};
