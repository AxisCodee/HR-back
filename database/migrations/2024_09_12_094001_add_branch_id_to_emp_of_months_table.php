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
        Schema::table('emp_of_months', function (Blueprint $table) {
            $table->foreignId('branch_id')->default(1)->constrained('branches');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('emp_of_months', function (Blueprint $table) {
            $table->dropColumn('branch_id');
        });
    }
};
