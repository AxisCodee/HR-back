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
        Schema::create('date_pins', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pin');
            $table->foreignId('date_id')->constrained('dates')->cascadeOnDelete();
            $table->foreign('pin')->references('id')->on('attendances')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('date_pins');
    }
};
