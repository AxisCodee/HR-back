<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->integer('pin');
            $table->timestamp('datetime');
            $table->string('verified', 255); // Specify appropriate length
            $table->string('status', 255);   // Specify appropriate length
            $table->string('work_code');
            $table->timestamps();

            // Create a unique constraint for pin, datetime, and status columns
            $table->unique(['pin', 'datetime', 'status'], 'unique_attendances_pin_datetime_status');

            // Add a generated column for the date part of the datetime column
            $table->date('date_part')->generatedAs('DATE(`datetime`)')->stored();

            // Index for the generated column
            $table->index(['date_part'], 'index_attendances_date');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
