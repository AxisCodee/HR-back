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
        Schema::create('lates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('check_in')->nullable(true);
            $table->string('check_out')->nullable(true);
            $table->date('lateDate')->nullable();
            $table->string('end')->nullable();
            $table->boolean('isPaid')->default(false);
            $table->boolean('demands_compensation')->default(false);
            $table->double('hours_num')->nullable(true)->default(NULL);
            $table->enum('status', ['waiting', 'accepted', 'rejected']);
            $table->enum('type', ['normal', 'Unjustified', 'justified']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lates');
    }
};
