<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** A picture per drafted stop: from Lea's gallery first, else Pexels (credited). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stops', function (Blueprint $table) {
            $table->string('photo_source', 16)->nullable()->after('thumb_url'); // gallery | pexels
            $table->string('photo_credit')->nullable()->after('photo_source');
            $table->string('photo_credit_url')->nullable()->after('photo_credit');
        });
    }

    public function down(): void
    {
        Schema::table('stops', function (Blueprint $table) {
            $table->dropColumn(['photo_source', 'photo_credit', 'photo_credit_url']);
        });
    }
};
