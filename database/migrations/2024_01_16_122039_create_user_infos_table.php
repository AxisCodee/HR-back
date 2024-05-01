<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_infos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->text('image')->nullable();
            $table->date('birth_date')->nullable();
            $table->date('start_date')->nullable();
            $table->enum('gender', ['Male', 'Female'])->nullable();
            $table->string('nationalID')->nullable();
            $table->enum('social_situation', ['Single', 'Married'])->nullable();
            $table->enum('military_situation', ['Postponed', 'Exempt', 'Finished'])->nullable();
            $table->enum('level', ['Senior', 'Mid', 'Junior'])->nullable();
            $table->string('health_status')->nullable();
            $table->bigInteger('salary')->nullable();
            $table->bigInteger('compensation_hours')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_infos');
    }
};
