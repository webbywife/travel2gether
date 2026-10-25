<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stops', function (Blueprint $table) {
            // Fixed stops of the arrival / departure day (landing, bag drop, checkout, airport, flight): an AI re-draft keeps them.
            $table->boolean('is_anchor')->default(false)->after('has_options');
        });
    }

    public function down(): void
    {
        Schema::table('stops', function (Blueprint $table) {
            $table->dropColumn('is_anchor');
        });
    }
};
