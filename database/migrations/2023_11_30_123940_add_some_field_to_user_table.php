<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar')->nullable();
            $table->string('slug')->nullable();
            $table->string('phone')->nullable();
            $table->enum('status', ['activated', 'pending', 'disabled'])->default('pending');
            $table->enum('verified', ['verified', 'not-verified'])->default('not-verified');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['avatar', 'slug', 'phone', 'status', 'verified']);
        });
    }
};
