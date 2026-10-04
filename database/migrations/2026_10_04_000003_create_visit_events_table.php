<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Anonymous launch funnel: which link brought people in (TikTok, Instagram…)
 * and how far they got. No IP, no account, no user agent — just a one-way
 * per-session code so a visitor counts once per step per day.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visit_events', function (Blueprint $table) {
            $table->id();
            $table->date('day');
            $table->string('source', 20);
            $table->string('event', 20);   // landing | sample | gallery | register | signup
            $table->char('visitor', 16);
            $table->boolean('mobile')->default(false);
            $table->timestamp('created_at')->nullable();
            $table->unique(['day', 'visitor', 'event']);
            $table->index(['day', 'source']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visit_events');
    }
};
