<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            // One free AI re-draft per trip (any day) — the *first* draft of a
            // day is never counted, only re-drafting a day that's already been
            // AI-drafted. Admins are exempt (see Trip::canRegenerate()).
            $table->unsignedInteger('regenerations_used')->default(0)->after('is_public');
        });
    }

    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropColumn('regenerations_used');
        });
    }
};
