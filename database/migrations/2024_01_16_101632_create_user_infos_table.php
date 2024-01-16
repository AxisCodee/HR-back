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
            $table->date('birth_date');
            $table->foreignId('address_id')->constrained('addresses')->cascadeOnDelete()->cascadeOnUpdate();
            $table->enum('gender', ['Male', 'Female']);
            $table->bigInteger('national_id');
            $table->enum('social_situation', ['Single', 'Married']);
            $table->enum('study_situation', ['None', 'High School', 'Institute', 'Bachelor\'s', 'Master']);
            $table->enum('military_situation', ['Postponed', 'Exempt', 'Finished']);
            $table->json('certificates')->nullable();
            $table->foreignId('career_id')->constrained('careers')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('deposit_id')->constrained('deposits')->cascadeOnDelete()->cascadeOnUpdate();
           //$table->foreignId('contact_id');
            $table->date('start_date')->default(now());
            $table->date('end_date');
            $table->bigInteger('salary');
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
