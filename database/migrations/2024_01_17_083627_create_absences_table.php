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
        Schema::create('absences', function (Blueprint $table) {
            $table->id();
            $table->enum('type',['justified','Unjustified'])->default('Unjustified');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('startDate')->nullable(true);
            $table->dateTime('endDate')->nullable(true);
            $table->enum('duration',['daily','hourly']);
            $table->enum('status',['waiting','accepted','rejected'])->nullable(false)->default('waiting');
            $table->integer('hours_num')->nullable(true)->default(NULL);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absences');
    }
};
