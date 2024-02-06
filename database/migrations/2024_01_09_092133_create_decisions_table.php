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
        Schema::create('decisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->enum('type', ['warning', 'reward', 'deduction', 'advanced','alert','penalty']);
            $table->string('content');
            $table->double('amount')->nullable(true)->default(null);
            $table->string('dateTime')->nullable(true)->default(null);
            $table->double('salary')->nullable(true)->default(null);
            $table->boolean('fromSystem')->default(false);



            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('decisions');
    }
};
