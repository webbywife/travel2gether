<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trip_invites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained()->cascadeOnDelete();
            $table->string('token', 64)->unique();
            $table->enum('role', ['editor', 'viewer'])->default('editor');
            $table->string('email')->nullable();                 // optional: pin the invite to one address
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->unsignedSmallInteger('max_uses')->nullable(); // null = unlimited
            $table->unsignedSmallInteger('uses')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trip_invites');
    }
};
