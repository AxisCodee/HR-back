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
        Schema::create('policies', function (Blueprint $table) {
            $table->id();
            $table->json('work_time');
            $table->json('annual_salary_increase');
            $table->json('warnings');
            $table->json('absence_management');
            $table->boolean('deduction_status')->default(true);
            $table->boolean('demands_compensation');
            $table->integer('monthlyhours')->default(0);
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ploicies');
    }
};
