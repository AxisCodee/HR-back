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
        Schema::create('rate_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rate_id')->constrained('rates')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('rateType_id')->constrained('rate_types')->cascadeOnDelete();
            $table->foreignId('evalutor_id')->constrained('users')->cascadeOnDelete();
            $table->integer('rate');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rate_users');
    }
};
