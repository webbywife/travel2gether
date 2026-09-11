<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // No payment processing yet — this is flipped by hand (tinker, or
            // an admin control later) until real billing exists. 'free' members
            // are capped (see User::FREE_TRIP_LIMIT, Trip::canRegenerate());
            // 'paid' and admins are not.
            $table->string('subscription', 16)->default('free')->after('avatar');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('subscription');
        });
    }
};
