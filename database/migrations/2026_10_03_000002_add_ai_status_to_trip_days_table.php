<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** AI day-fill now runs in the background — track where each day's draft is. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trip_days', function (Blueprint $table) {
            $table->string('ai_status', 16)->nullable()->after('source'); // queued | running | failed
            $table->string('ai_error')->nullable()->after('ai_status');
        });
    }

    public function down(): void
    {
        Schema::table('trip_days', function (Blueprint $table) {
            $table->dropColumn(['ai_status', 'ai_error']);
        });
    }
};
